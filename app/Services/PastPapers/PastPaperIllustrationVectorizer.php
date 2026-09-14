<?php

namespace App\Services\PastPapers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * Reads rendered source-paper pages with a vision model and recreates only the
 * question diagrams as safe, self-contained SVG. This is deliberately separate
 * from text extraction: PDF-to-text models routinely omit diagrams.
 */
class PastPaperIllustrationVectorizer
{
    /** @return array{draft:array<string,mixed>,generated:int,review:int,skipped:int} */
    public function vectorise(array $draft, ?int $onlyPage = null, bool $overwrite = false): array
    {
        $questions = (array) data_get($draft, 'draft.questions', []);
        $filename = basename((string) ($draft['filename'] ?? ''));
        if ($filename === '' || $questions === []) {
            return compact('draft') + ['generated' => 0, 'review' => 0, 'skipped' => 0];
        }

        $byPage = collect($questions)->groupBy(fn (array $question) => max(1, (int) ($question['source_page'] ?? 1)));
        if ($onlyPage !== null) {
            $byPage = $byPage->only($onlyPage);
        }

        $generated = 0;
        $review = 0;
        $skipped = 0;
        foreach ($byPage as $page => $pageQuestions) {
            $path = 'past-paper-source/rendered/'.sha1($filename).'/page-'.str_pad((string) $page, 3, '0', STR_PAD_LEFT).'.png';
            if (! Storage::disk('local')->exists($path)) {
                $skipped += $pageQuestions->count();
                continue;
            }

            $results = $this->vectorisePage(Storage::disk('local')->path($path), $pageQuestions->values()->all());
            foreach ($results as $number => $svg) {
                foreach ($questions as $index => $question) {
                    if ((int) ($question['number'] ?? 0) !== (int) $number) {
                        continue;
                    }
                    if (! $overwrite && ! empty($question['illustration_svg'])) {
                        continue;
                    }
                    $questions[$index]['illustration_svg'] = $svg;
                    $questions[$index]['needs_review'] = true;
                    $generated++;
                    break;
                }
            }

            // The page is still a human-QC item even when the model sees no diagram.
            $review += $pageQuestions->count() - count($results);
        }

        data_set($draft, 'draft.questions', $questions);

        return compact('draft', 'generated', 'review', 'skipped');
    }

    /** @param array<int, array<string,mixed>> $questions @return array<int,string> */
    private function vectorisePage(string $path, array $questions): array
    {
        $key = (string) config('services.llm.key');
        $base = rtrim((string) config('services.llm.base_url'), '/');
        $models = array_values(array_filter(array_unique(array_merge(
            [(string) config('services.llm.past_paper_illustration_model')],
            (array) config('services.llm.past_paper_illustration_fallback_models', []),
        ))));
        if ($key === '' || $base === '' || $models === []) {
            return [];
        }

        $bytes = @file_get_contents($path);
        if ($bytes === false) {
            return [];
        }

        $questionList = collect($questions)->map(fn (array $q) => [
            'number' => (int) ($q['number'] ?? 0),
            'prompt' => (string) ($q['prompt'] ?? ''),
        ])->values()->all();
        $prompt = <<<'TXT'
You are digitising a real SEA Mathematics/ELA paper page. Inspect the supplied page image.

For each listed question, create an illustration ONLY when its answer depends on a visible diagram, graph, table, number line, clock, net, or chart on this page. Recreate that visual faithfully as simple, self-contained SVG: include every label, value, arrow, line, shape, and data point needed to answer. Do not create a generic substitute. If the visual is absent, ambiguous, photographic, or you cannot faithfully recreate it, omit that question entirely.

SVG rules: use a viewBox, inline elements only (svg, g, path, line, rect, circle, ellipse, polygon, polyline, text, defs, marker); no images, scripts, styles, external URLs, or foreignObject.

Return JSON only: {"illustrations":[{"number":7,"svg":"<svg ...>...</svg>"}]}

Questions on this page:
TXT;

        foreach ($models as $model) {
            try {
                $response = Http::withToken($key)->timeout(90)->post($base.'/chat/completions', [
                    'model' => $model,
                    'max_tokens' => 5000,
                    'temperature' => 0,
                    'messages' => [[
                        'role' => 'user',
                        'content' => [
                            ['type' => 'text', 'text' => $prompt."\n".json_encode($questionList, JSON_UNESCAPED_SLASHES)],
                            ['type' => 'image_url', 'image_url' => ['url' => 'data:image/png;base64,'.base64_encode($bytes)]],
                        ],
                    ]],
                    'provider' => ['allow_fallbacks' => true],
                    'usage' => ['include' => true],
                ]);
                if ($response->failed()) {
                    Log::warning('Past-paper SVG generation failed', ['status' => $response->status(), 'model' => $model]);
                    continue;
                }

                $decoded = $this->decode((string) ($response->json('choices.0.message.content') ?? ''));
                $allowed = collect($questionList)->pluck('number')->flip();
                $out = [];
                foreach ((array) ($decoded['illustrations'] ?? []) as $item) {
                    $number = (int) ($item['number'] ?? 0);
                    $svg = SvgSanitizer::clean($item['svg'] ?? null);
                    if ($number > 0 && $allowed->has($number) && $svg !== null) {
                        $out[$number] = $svg;
                    }
                }

                return $out;
            } catch (\Throwable $e) {
                Log::warning('Past-paper SVG generation exception: '.$e->getMessage());
            }
        }

        return [];
    }

    /** @return array<string,mixed> */
    private function decode(string $raw): array
    {
        $start = strpos($raw, '{');
        $end = strrpos($raw, '}');
        if ($start === false || $end === false || $end <= $start) {
            return [];
        }

        $decoded = json_decode(substr($raw, $start, $end - $start + 1), true);
        return is_array($decoded) ? $decoded : [];
    }
}
