<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

/**
 * SJ-07 tooling — benchmark vision models on the school-journal OCR task.
 *
 * Sends one graded-assessment photo to each candidate model with the exact
 * extraction prompt OcrService uses, scores the structured reply against the
 * ground truth, and prints a cost/accuracy table so the primary + fallback
 * vision models can be chosen from data, not vibes.
 *
 * Usage:
 *   php artisan sj:ocr-benchmark --image=/path/to/graded_paper.jpg
 *   php artisan sj:ocr-benchmark --models=m1,m2,m3 --image=...
 *   php artisan sj:ocr-benchmark --list   (show the default candidate set)
 */
class SjOcrBenchmark extends Command
{
    protected $signature = 'sj:ocr-benchmark
        {--image= : Path to the graded-paper JPG (default: tests/fixtures/graded_paper.jpg)}
        {--models= : Comma-separated OpenRouter model ids (default: curated candidate set)}
        {--list : Just list the default candidates and exit}';

    protected $description = 'Benchmark vision models on the school-journal OCR task (SJ-07)';

    /** Curated candidates: cheapest ~28 chat-vision models + proven baselines. */
    private const CANDIDATES = [
        'dots-studio/dots-3-note-preview:free',
        'google/gemma-4-26b-a4b-it:free',
        'nvidia/nemotron-nano-12b-v2-vl:free',
        'nex-agi/nex-n2-mini',
        'qwen/qwen3.7-flash',
        'openai/gpt-5-nano',
        'google/gemma-3-4b-it',
        'google/gemma-3-12b-it',
        'amazon/nova-lite-v1',
        'qwen/qwen3.5-flash-02-23',
        'google/gemma-4-26b-a4b-it',
        'bytedance-seed/seed-1.6-flash',
        'google/gemma-3-27b-it',
        'mistralai/mistral-small-3.2-24b-instruct',
        'google/gemma-4-31b-it',
        'rekaai/reka-edge',
        'qwen/qwen3.5-9b',
        'bytedance-seed/seed-2.0-mini',
        'mistralai/ministral-3b-2512',
        'bytedance/ui-tars-1.5-7b',
        'google/gemini-2.5-flash-lite',
        'openai/gpt-4.1-nano',
        'meta-llama/llama-4-scout',
        'qwen/qwen3-vl-32b-instruct',
        'qwen/qwen3-vl-8b-instruct',
        'openai/gpt-4o-mini',
        'google/gemini-2.5-flash',
        'openai/gpt-4o-mini',
    ];

    private const GROUND_TRUTH = [
        'score' => '18/20',
        'strand' => 'grammar',
        'assessment_type' => 'test',
        'comment_terms' => ['plural', 'great effort'],
        'text_terms' => ['stories', 'aaliyah'],
    ];

    public function handle(): int
    {
        if ($this->option('list')) {
            $this->table(['#', 'model'], array_map(fn ($m, $i) => [$i + 1, $m], self::CANDIDATES, array_keys(self::CANDIDATES)));

            return self::SUCCESS;
        }

        $imagePath = (string) ($this->option('image') ?: base_path('tests/fixtures/graded_paper.jpg'));
        if (! is_file($imagePath)) {
            $this->error('Provide --image=<path to a graded-paper photo>.');

            return self::FAILURE;
        }

        $models = $this->option('models')
            ? array_values(array_filter(array_map('trim', explode(',', (string) $this->option('models')))))
            : array_values(array_unique(self::CANDIDATES));

        $dataUrl = 'data:image/jpeg;base64,'.base64_encode((string) file_get_contents($imagePath));
        $key = (string) config('services.llm.key');
        $baseUrl = rtrim((string) config('services.llm.base_url'), '/');
        if ($key === '' || $baseUrl === '') {
            $this->error('LLM key/base URL not configured.');

            return self::FAILURE;
        }

        $system = $this->extractionPrompt();

        $rows = [];
        foreach ($models as $model) {
            $this->line("→ {$model}");
            $started = microtime(true);
            $payload = $this->visionRequest($baseUrl, $key, $model, $system, $dataUrl);
            $latency = (int) round((microtime(true) - $started) * 1000);

            $rows[] = $this->score($model, $payload, $latency);
            usleep(500000); // be polite to the router
        }

        usort($rows, fn (array $a, array $b) => [$b['ok'], $b['acc'], $a['cost']] <=> [$a['ok'], $a['acc'], $b['cost']]);

        $this->newLine();
        $this->table(
            ['model', 'json', 'score', 'strand', 'type', 'comment', 'text', 'acc', 'ms', 'cost$'],
            array_map(fn (array $r) => [
                $r['model'],
                $r['ok'] ? '✓' : '✗',
                $r['score'] ? '✓' : '✗',
                $r['strand'] ? '✓' : '✗',
                $r['type'] ? '✓' : '✗',
                $r['comment'] ? '✓' : '✗',
                $r['text'] ? '✓' : '✗',
                $r['acc'].'/6',
                number_format($r['ms'] / 1000, 1),
                number_format($r['cost'], 6),
            ], $rows),
        );

        $good = array_values(array_filter($rows, fn (array $r) => $r['ok'] && $r['score'] && $r['acc'] >= 5));
        if ($good !== []) {
            usort($good, fn (array $a, array $b) => $a['cost'] <=> $b['cost']);
            $this->newLine();
            $this->info('Cheapest accurate three (primary + fallbacks):');
            foreach (array_slice($good, 0, 3) as $pick) {
                $this->line('  • '.$pick['model'].' — $'.number_format($pick['cost'], 6).', '.$pick['acc'].'/6');
            }
        } else {
            $this->warn('No model met the accuracy bar — inspect the table.');
        }

        return self::SUCCESS;
    }

    /** @return array{content:string|null, cost:float} */
    private function visionRequest(string $baseUrl, string $key, string $model, string $system, string $dataUrl): array
    {
        $body = [
            'model' => $model,
            'max_tokens' => 900,
            'messages' => [
                ['role' => 'system', 'content' => $system],
                ['role' => 'user', 'content' => [
                    ['type' => 'text', 'text' => 'Digitise this graded assessment.'],
                    ['type' => 'image_url', 'image_url' => ['url' => $dataUrl]],
                ]],
            ],
            'usage' => ['include' => true],
        ];

        foreach ([0, 1] as $attempt) {
            try {
                $response = Http::withToken($key)->timeout(60)->post("{$baseUrl}/chat/completions", $body);

                if ($response->status() === 429 || $response->status() >= 500) {
                    sleep(8);

                    continue;
                }

                return [
                    'content' => $response->json('choices.0.message.content'),
                    'cost' => (float) ($response->json('usage.cost') ?? 0),
                ];
            } catch (\Throwable $e) {
                if ($attempt === 1) {
                    return ['content' => null, 'cost' => 0.0];
                }
                sleep(4);
            }
        }

        return ['content' => null, 'cost' => 0.0];
    }

    /** @return array<string, mixed> */
    private function score(string $model, array $payload, int $latency): array
    {
        $raw = (string) ($payload['content'] ?? '');
        $start = strpos($raw, '{');
        $end = strrpos($raw, '}');
        $decoded = ($start !== false && $end !== false)
            ? json_decode(substr($raw, $start, $end - $start + 1), true)
            : null;

        if (! is_array($decoded)) {
            return ['model' => $model, 'ok' => false, 'score' => false, 'strand' => false, 'type' => false, 'comment' => false, 'text' => false, 'acc' => 0, 'ms' => $latency, 'cost' => $payload['cost']];
        }

        $norm = fn (?string $v) => strtolower(trim((string) $v));
        $haystack = $norm($raw);

        $scoreOk = $norm($decoded['score'] ?? null) === self::GROUND_TRUTH['score'];
        $strandOk = str_contains($norm($decoded['strand'] ?? null), self::GROUND_TRUTH['strand']);
        $typeOk = str_contains($norm($decoded['assessment_type'] ?? null), self::GROUND_TRUTH['assessment_type']);
        $commentOk = (bool) array_filter(
            self::GROUND_TRUTH['comment_terms'],
            fn (string $term) => str_contains($norm($decoded['teacher_comment'] ?? null), $term),
        );
        $textOk = (bool) array_filter(
            self::GROUND_TRUTH['text_terms'],
            fn (string $term) => str_contains($norm($decoded['text'] ?? ''), $term),
        ) || str_contains($haystack, 'stories');

        $acc = (int) $scoreOk + (int) $strandOk + (int) $typeOk + (int) $commentOk + (int) $textOk + 1 /* JSON parse */;

        return [
            'model' => $model,
            'ok' => true,
            'score' => $scoreOk,
            'strand' => $strandOk,
            'type' => $typeOk,
            'comment' => $commentOk,
            'text' => $textOk,
            'acc' => $acc,
            'ms' => $latency,
            'cost' => $payload['cost'],
        ];
    }

    /** The exact extraction contract OcrService sends — one source of truth in spirit. */
    private function extractionPrompt(): string
    {
        return <<<'TXT'
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
    }
}
