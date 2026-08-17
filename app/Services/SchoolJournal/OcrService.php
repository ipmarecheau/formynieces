<?php

namespace App\Services\SchoolJournal;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * SJ-07 — the digitisation seam behind the school journal. Today it reads a
 * photo of a graded assessment with an LLM-vision call (structured fields +
 * per-field confidence); tomorrow a self-hosted CNN/HTR model can replace the
 * internals without the journal flow changing — callers only see digitize().
 *
 * Design notes:
 * - Images are downscaled before sending (cheaper tokens, better accuracy, SJ-07 cost guard).
 * - Every field carries a confidence; anything below the review threshold is
 *   surfaced for human confirmation rather than trusted blindly (SJ-07).
 * - Any failure returns null — the entry simply stays `pending` and the guardian
 *   fills the fields by hand. Digitisation is a convenience, never a dependency.
 */
class OcrService
{
    /** Fields the pipeline extracts, with the confidence floor each must clear. */
    private const FIELDS = ['subject', 'strand', 'assessment_type', 'score', 'teacher_comment'];

    private const REVIEW_THRESHOLD = 0.70;

    private const MAX_EDGE = 1024;

    /**
     * Digitise an assessment image. Returns
     * array{fields: array<string,?string>, text: string, confidence: array<string,float>, review: array<int,string>}
     * or null when it could not run (unsupported type, vision call failed, …).
     *
     * @return array<string, mixed>|null
     */
    public function digitize(string $absolutePath, string $mime): ?array
    {
        if (! in_array($mime, ['image/jpeg', 'image/png', 'image/webp'], true)) {
            return null; // PDFs and others wait for manual entry — honest, not broken.
        }

        $encoded = $this->base64Image($absolutePath, $mime);
        if ($encoded === null) {
            return null;
        }

        $payload = $this->visionCall($encoded, $mime);
        if ($payload === null) {
            return null;
        }

        $fields = [];
        $confidence = [];
        $review = [];
        foreach (self::FIELDS as $field) {
            $value = isset($payload[$field]) && is_string($payload[$field]) && $payload[$field] !== ''
                ? trim($payload[$field])
                : null;
            $score = isset($payload['confidence'][$field]) ? (float) $payload['confidence'][$field] : 0.0;
            $fields[$field] = $value;
            $confidence[$field] = round($score, 2);
            if ($value !== null && $score < self::REVIEW_THRESHOLD) {
                $review[] = $field;
            }
        }

        return [
            'fields' => $fields,
            'text' => is_string($payload['text'] ?? null) ? $payload['text'] : '',
            'confidence' => $confidence,
            'review' => $review,
        ];
    }

    /**
     * Downscale to a sane edge and base64-encode. Falls back to the original
     * bytes when GD is unavailable — cheaper to avoid, never a blocker.
     */
    private function base64Image(string $path, string $mime): ?string
    {
        try {
            $data = @file_get_contents($path);
            if ($data === false) {
                return null;
            }

            if (! function_exists('imagecreatetruecolor')) {
                return 'data:'.$mime.';base64,'.base64_encode($data);
            }

            $image = match ($mime) {
                'image/jpeg' => @imagecreatefromstring($data),
                'image/png' => @imagecreatefromstring($data),
                'image/webp' => @imagecreatefromstring($data),
                default => null,
            };
            if ($image === false || $image === null) {
                return null;
            }

            $w = imagesx($image);
            $h = imagesy($image);
            $scale = min(1, self::MAX_EDGE / max($w, $h));
            if ($scale < 1) {
                $nw = (int) round($w * $scale);
                $nh = (int) round($h * $scale);
                $resized = imagecreatetruecolor($nw, $nh);
                imagecopyresampled($resized, $image, 0, 0, 0, 0, $nw, $nh, $w, $h);
                imagedestroy($image);
                $image = $resized;
            }

            ob_start();
            imagejpeg($image, null, 85);
            $bytes = (string) ob_get_clean();
            imagedestroy($image);

            return 'data:image/jpeg;base64,'.base64_encode($bytes);
        } catch (\Throwable $e) {
            Log::warning('OCR image prep failed: '.$e->getMessage());

            return null;
        }
    }

    /**
     * The LLM-vision call. Kept private and small so the whole backend is one
     * method to swap for a CNN some day.
     *
     * @return array<string, mixed>|null
     */
    private function visionCall(string $dataUrl, string $mime): ?array
    {
        $key = (string) config('services.llm.key');
        $baseUrl = rtrim((string) config('services.llm.base_url'), '');
        $model = (string) config('services.llm.vision_model');
        if ($key === '' || $baseUrl === '' || $model === '') {
            return null; // not configured — manual entry path takes over
        }

        $system = <<<'TXT'
        You read photos of graded school assessments (Caribbean primary school, SEA syllabus).
        Extract structured fields and rate how certain you are for EACH field, 0.00 to 1.00.
        Rules:
        - score: exactly as written, e.g. "18/20" or "85%". If unreadable, null.
        - strand: the skill tested, in SEA terms if possible (e.g. "Grammar", "Number", "Vocabulary").
        - teacher_comment: the teacher's written remark, verbatim if readable.
        - Never guess. Low certainty means a LOW confidence number, not a plausible invention.
        Respond with valid JSON only: {"subject":..,"strand":..,"assessment_type":..,"score":..,
        "teacher_comment":..,"text":"<full transcribed text>","confidence":{"subject":0.0,...}}
        TXT;

        $messages = [[
            'role' => 'user',
            'content' => [
                ['type' => 'text', 'text' => 'Digitise this graded assessment.'],
                ['type' => 'image_url', 'image_url' => ['url' => $dataUrl]],
            ],
        ]];

        try {
            $response = Http::withToken($key)
                ->timeout(45)
                ->post(rtrim($baseUrl, '/').'/chat/completions', [
                    'model' => $model,
                    'max_tokens' => 900,
                    'messages' => array_merge([['role' => 'system', 'content' => $system]], $messages),
                    'usage' => ['include' => true],
                ]);

            if ($response->failed()) {
                Log::error('OCR vision call failed', ['status' => $response->status()]);

                return null;
            }

            $raw = (string) ($response->json('choices.0.message.content') ?? '');
            $start = strpos($raw, '{');
            $end = strrpos($raw, '}');
            if ($start === false || $end === false) {
                return null;
            }

            $decoded = json_decode(substr($raw, $start, $end - $start + 1), true);
            if (! is_array($decoded)) {
                return null;
            }

            return $decoded;
        } catch (\Throwable $e) {
            Log::warning('OCR vision exception: '.$e->getMessage());

            return null;
        }
    }
}
