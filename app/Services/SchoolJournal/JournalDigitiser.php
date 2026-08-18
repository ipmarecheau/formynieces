<?php

namespace App\Services\SchoolJournal;

use App\Models\SchoolJournalEntry;
use App\Models\SchoolJournalQuestion;
use Illuminate\Support\Facades\Storage;

/**
 * SJ-11..13 — persists what the OCR seam read: fills the entry's header
 * fields, stores each question with its syllabus alignment (TopicMatcher),
 * saves a clipped image per question when possible (GD-optional — a box is
 * always stored so the UI can clip client-side), and never lets a clipping
 * failure break the filing.
 */
class JournalDigitiser
{
    public function __construct(
        private OcrService $ocr,
        private TopicMatcher $matcher,
    ) {}

    /**
     * Digitise a freshly-filed entry in place. Returns true when the pipeline
     * ran (fields/questions stored), false when it could not — in which case
     * the entry stays pending for manual entry.
     */
    public function digitise(SchoolJournalEntry $entry, string $mime): bool
    {
        $disk = Storage::disk('local');
        $result = $this->ocr->digitize($disk->path($entry->image_path), $mime);

        if ($result === null) {
            return false;
        }

        $entry->fill($result['fields']);
        $entry->ocr_text = $result['text'];
        $entry->ocr_confidence = $result['confidence'];
        $entry->digitisation_status = SchoolJournalEntry::STATUS_DIGITISED;
        $entry->save();

        $subject = $result['fields']['subject'] ?? null;

        foreach ((array) ($result['questions'] ?? []) as $q) {
            $this->storeQuestion($entry, $q, $subject);
        }

        return true;
    }

    /** @param array<string, mixed> $q */
    private function storeQuestion(SchoolJournalEntry $entry, array $q, ?string $subject): void
    {
        $alignment = $this->matcher->match(
            $q['topic'] ?? null,
            $q['module_code'] ?? null,
            $subject,
        );

        $box = $this->validBox($q['box'] ?? null);

        SchoolJournalQuestion::create([
            'school_journal_entry_id' => $entry->id,
            'number' => isset($q['number']) ? (int) $q['number'] : null,
            'prompt' => $this->nullableString($q['prompt'] ?? null),
            'student_answer' => $this->nullableString($q['student_answer'] ?? null),
            'correct_answer' => $this->nullableString($q['correct_answer'] ?? null),
            'is_correct' => isset($q['is_correct']) ? (bool) $q['is_correct'] : null,
            'syllabus_module_id' => $alignment['module']?->id,
            'topic_label' => $this->nullableString($q['topic'] ?? null),
            'topic_confidence' => $alignment['confidence'],
            'reasoning_note' => $this->nullableString($q['reasoning_note'] ?? null),
            'clip_path' => $box !== null ? $this->clip($entry, $box) : null,
            'clip_box' => $box,
        ]);
    }

    /**
     * A screenshot of the question + its solution (SJ-12). GD-optional: when
     * the extension is missing the box is stored and the UI clips client-side,
     * so a clip never depends on an extension we may not have.
     *
     * @param  array{0:int,1:int,2:int,3:int}  $box
     */
    private function clip(SchoolJournalEntry $entry, array $box): ?string
    {
        if (! function_exists('imagecreatetruecolor')) {
            return null;
        }

        try {
            $disk = Storage::disk('local');
            $source = $disk->path($entry->image_path);
            $image = @imagecreatefromstring((string) file_get_contents($source));
            if ($image === false) {
                return null;
            }

            $w = imagesx($image);
            $h = imagesy($image);
            $x1 = (int) floor($box[0] / 1000 * $w);
            $y1 = (int) floor($box[1] / 1000 * $h);
            $x2 = (int) ceil($box[2] / 1000 * $w);
            $y2 = (int) ceil($box[3] / 1000 * $h);
            $cw = max(1, min($w, $x2) - max(0, $x1));
            $ch = max(1, min($h, $y2) - max(0, $y1));

            $crop = imagecrop($image, ['x' => max(0, $x1), 'y' => max(0, $y1), 'width' => $cw, 'height' => $ch]);
            if ($crop === false) {
                return null;
            }

            $path = dirname($entry->image_path)."/q{$entry->id}-".substr((string) uniqid(), -6).'.jpg';
            ob_start();
            imagejpeg($crop, null, 88);
            $bytes = (string) ob_get_clean();
            imagedestroy($crop);
            imagedestroy($image);

            $disk->put($path, $bytes);

            return $path;
        } catch (\Throwable $e) {
            return null; // SJ-12: a broken clip never breaks the filing
        }
    }

    /** @return array{0:int,1:int,2:int,3:int}|null */
    private function validBox(mixed $box): ?array
    {
        if (! is_array($box) || count($box) !== 4) {
            return null;
        }

        $ints = array_map(fn ($v) => (int) $v, array_values($box));
        [$x1, $y1, $x2, $y2] = $ints;
        if ($x1 < 0 || $y1 < 0 || $x2 > 1000 || $y2 > 1000 || $x2 - $x1 < 20 || $y2 - $y1 < 20) {
            return null; // junk box — full page fallback
        }

        return [$x1, $y1, $x2, $y2];
    }

    private function nullableString(mixed $value): ?string
    {
        return is_string($value) && trim($value) !== '' ? trim($value) : null;
    }
}
