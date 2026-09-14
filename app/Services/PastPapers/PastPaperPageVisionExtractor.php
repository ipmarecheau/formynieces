<?php

namespace App\Services\PastPapers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * The source-of-truth intake path for scanned and diagram-heavy papers. Each
 * rendered page is read independently, so a missed diagram cannot shift every
 * later question onto the wrong source page.
 */
class PastPaperPageVisionExtractor
{
    /** @return array{draft:array<string,mixed>,questions:int,diagrams:int,skipped:int} */
    public function rebuild(array $draft, ?int $onlyPage = null): array
    {
        $filename = basename((string) ($draft['filename'] ?? ''));
        if ($filename === '') {
            return compact('draft') + ['questions' => 0, 'diagrams' => 0, 'skipped' => 0];
        }

        $directory = 'past-paper-source/rendered/'.sha1($filename);
        $pages = collect(Storage::disk('local')->files($directory))
            ->filter(fn (string $path) => preg_match('/page-(\d{3})\.png$/', $path) === 1)
            ->sort()
            ->values();
        if ($onlyPage !== null) {
            $needle = 'page-'.str_pad((string) $onlyPage, 3, '0', STR_PAD_LEFT).'.png';
            $pages = $pages->filter(fn (string $path) => str_ends_with($path, $needle))->values();
        }

        $fresh = [];
        $skipped = 0;
        foreach ($pages as $path) {
            preg_match('/page-(\d{3})\.png$/', $path, $match);
            $page = (int) ($match[1] ?? 0);
            $items = $this->extractPage(Storage::disk('local')->path($path), $page);
            if ($items === null) {
                $skipped++;
                continue;
            }
            foreach ($items as $item) {
                $fresh[(int) $item['number']] = $item;
            }
        }

        if ($onlyPage !== null) {
            $rebuiltNumbers = array_keys($fresh);
            foreach ((array) data_get($draft, 'draft.questions', []) as $question) {
                if ((int) ($question['source_page'] ?? 1) !== $onlyPage && ! in_array((int) ($question['number'] ?? 0), $rebuiltNumbers, true)) {
                    $fresh[(int) ($question['number'] ?? 0)] = $question;
                }
            }
        }

        $questions = collect($fresh)->filter(fn (array $question, int $number) => $number > 0)
            ->sortBy('number')->values()->all();
        if ($questions !== []) {
            data_set($draft, 'draft.questions', $questions);
        }

        return [
            'draft' => $draft,
            'questions' => count($questions),
            'diagrams' => collect($questions)->filter(fn (array $q) => SvgSanitizer::clean($q['illustration_svg'] ?? null) !== null)->count(),
            'skipped' => $skipped,
        ];
    }

    /** @return array<int,array<string,mixed>>|null */
    private function extractPage(string $path, int $page): ?array
    {
        $key = (string) config('services.llm.key');
        $base = rtrim((string) config('services.llm.base_url'), '/');
        $models = array_values(array_filter(array_unique(array_merge(
            [(string) config('services.llm.past_paper_illustration_model')],
            (array) config('services.llm.past_paper_illustration_fallback_models', []),
        ))));
        $bytes = @file_get_contents($path);
        if ($key === '' || $base === '' || $models === [] || $bytes === false) {
            return null;
        }

        foreach ($models as $model) {
            try {
                $response = Http::withToken($key)->timeout(90)->post($base.'/chat/completions', [
                    'model' => $model,
                    'max_tokens' => 7000,
                    'temperature' => 0,
                    'messages' => [[
                        'role' => 'user',
                        'content' => [
                            ['type' => 'text', 'text' => $this->prompt($page)],
                            ['type' => 'image_url', 'image_url' => ['url' => 'data:image/png;base64,'.base64_encode($bytes)]],
                        ],
                    ]],
                    'provider' => ['allow_fallbacks' => true],
                    'usage' => ['include' => true],
                ]);
                if ($response->failed()) {
                    Log::warning('Past-paper page extraction failed', ['status' => $response->status(), 'model' => $model, 'page' => $page]);
                    continue;
                }

                $decoded = $this->decode((string) ($response->json('choices.0.message.content') ?? ''));
                if (! is_array($decoded['questions'] ?? null)) {
                    continue;
                }

                return collect($decoded['questions'])->map(function (mixed $item) use ($page): ?array {
                    if (! is_array($item) || (int) ($item['number'] ?? 0) < 1 || blank($item['prompt'] ?? null)) {
                        return null;
                    }

                    return [
                        'number' => (int) $item['number'],
                        'prompt' => trim((string) $item['prompt']),
                        'options' => array_values(array_filter((array) ($item['options'] ?? []), 'is_string')),
                        'correct_answer' => null,
                        'marks' => max(1, (int) ($item['marks'] ?? 1)),
                        'topic' => null,
                        'module_code' => null,
                        'objective' => null,
                        'difficulty' => null,
                        'source_page' => $page,
                        'illustration_svg' => SvgSanitizer::clean($item['illustration_svg'] ?? null),
                        'confidence' => is_numeric($item['confidence'] ?? null) ? (float) $item['confidence'] : 0.0,
                        'needs_review' => true,
                    ];
                })->filter()->values()->all();
            } catch (\Throwable $e) {
                Log::warning('Past-paper page extraction exception: '.$e->getMessage());
            }
        }

        return null;
    }

    private function prompt(int $page): string
    {
        return <<<TXT
You are digitising page {$page} of a real Trinidad and Tobago SEA paper. Read only what is visibly present on this image.

Return every printed numbered test question on this page, using its printed number exactly. Transcribe the prompt faithfully, including units and values. Do not make up an answer key. Keep multiple-choice options only when they are visible.

For a question whose answer depends on a visible diagram, graph, table, clock, net, chart, or number line, recreate that exact visual as a self-contained SVG. Preserve labels, measurements, arrows, plotted values, bars, sectors, and shapes. If a visual is unclear, set illustration_svg to null; never invent a diagram described only by the text.

SVG rules: include a viewBox; use only inline svg, g, path, line, rect, circle, ellipse, polygon, polyline, text, defs, marker; no images, scripts, styles, URLs, or foreignObject.

Return JSON only:
{"questions":[{"number":1,"prompt":"","options":[],"marks":1,"illustration_svg":null,"confidence":0.0}]}
TXT;
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
