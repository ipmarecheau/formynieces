<?php

namespace App\Services\PastPapers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/** Extracts a small source PDF into an unapproved JSON draft. */
class PastPaperPdfExtractor
{
    public function extract(string $absolutePath, string $filename): ?array
    {
        $key = (string) config('services.llm.key');
        $base = rtrim((string) config('services.llm.base_url'), '/');
        $model = (string) config('services.llm.past_paper_model');
        if ($key === '' || $base === '' || $model === '') return null;
        $data = @file_get_contents($absolutePath);
        if ($data === false) return null;
        $models = array_slice(array_values(array_unique(array_merge([$model], (array) config('services.llm.past_paper_fallback_models', [])))), 0, 3);
        $prompt = 'Extract this SEA source paper into draft questions. Return JSON only: {"paper":{"subject":"Math|ELA","year":null,"paper_type":"multiple_choice|creative_writing"},"questions":[{"number":1,"prompt":"","options":[],"correct_answer":null,"marks":1,"topic":null,"module_code":null,"objective":null,"difficulty":null,"source_page":1,"illustration_svg":null,"confidence":0.0,"needs_review":true}]}. Preserve wording and do not invent answer keys. If an answer key is absent, use null and needs_review true. For diagrams, geometry, number lines, tables, or charts, recreate only simple visual information as valid self-contained SVG using basic shapes and text; use null for photographs or complex illustrations. Every question is a draft for human QC.';
        foreach ($models as $candidate) {
            try {
                $response = Http::withToken($key)->timeout(120)->post($base.'/chat/completions', [
                    'model' => $candidate, 'max_tokens' => 6000, 'temperature' => 0,
                    'messages' => [['role' => 'user', 'content' => [
                        ['type' => 'text', 'text' => $prompt],
                        ['type' => 'file', 'file' => ['filename' => $filename, 'file_data' => 'data:application/pdf;base64,'.base64_encode($data)]],
                    ]]],
                    'provider' => ['allow_fallbacks' => true], 'usage' => ['include' => true],
                ]);
                if ($response->failed()) continue;
                $raw = (string) ($response->json('choices.0.message.content') ?? '');
                $start = strpos($raw, '{'); $end = strrpos($raw, '}');
                if ($start === false || $end === false) continue;
                $result = json_decode(substr($raw, $start, $end - $start + 1), true);
                if (is_array($result) && is_array($result['questions'] ?? null)) {
                    return ['model' => $candidate, 'filename' => $filename, 'draft' => $result];
                }
            } catch (\Throwable $e) { Log::warning('Past paper extraction failed: '.$e->getMessage()); }
        }
        return null;
    }
}
