<?php

namespace App\Console\Commands;

use App\Services\LlmService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

/**
 * Educator (first-principles) audit. A single expert-teacher agent judges each lesson on whether it
 * teaches the concept from first principles, states rules generally, names conventions as
 * conventions, uses a concrete/visual model, and speaks in child-safe language — and recommends the
 * one visual widget that best fits the concept. Grounds its judgement on reputable sources.
 *
 * Read-only: writes reports to storage/app/soundness-audit/.
 */
class LessonsSoundnessAudit extends Command
{
    protected $signature = 'lessons:soundness-audit {module?} {--fresh}';

    protected $description = 'Educator audit: first-principles, generality, conventions, visual model, child-safe language';

    private const OUT_DIR = 'soundness-audit';

    public function handle(LlmService $llm): int
    {
        $catalogue = collect(json_decode(File::get(base_path('database/data/objective_catalogue.json')), true))->keyBy('lesson_code');
        $files = collect(File::files(base_path('database/data/lessons')))
            ->filter(fn ($f) => $f->getExtension() === 'json')
            ->sortBy(fn ($f) => $f->getFilename())->values();

        $only = $this->argument('module');
        $outDir = storage_path('app/'.self::OUT_DIR);
        File::ensureDirectoryExists($outDir);

        $summary = [];
        foreach ($files as $file) {
            $raw = json_decode(File::get($file->getRealPath()), true);
            $lesson = isset($raw['blocks']) ? $raw : ($raw[0] ?? null);
            if (! $lesson) {
                continue;
            }
            $module = $lesson['module'] ?? $file->getFilename();
            if ($only && strcasecmp($module, $only) !== 0) {
                continue;
            }

            $path = $outDir.'/'.Str::slug($module).'.json';
            if (! $this->option('fresh') && File::exists($path)) {
                $summary[] = json_decode(File::get($path), true);
                $this->line("· {$module} (cached)");

                continue;
            }

            $this->line("→ {$module}");
            $report = $this->audit($llm, $lesson, $catalogue->get($module));
            $report['module'] = $module;
            $report['title'] = $lesson['title'] ?? '';
            File::put($path, json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
            $summary[] = $report;
        }

        File::put($outDir.'/_summary.json', json_encode($summary, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
        $this->renderTable($summary);
        $this->info(count($summary).' lessons audited → '.$outDir);

        return self::SUCCESS;
    }

    /** @return array<string,mixed> */
    private function audit(LlmService $llm, array $lesson, ?array $meta): array
    {
        $objective = $meta['objective'] ?? ($lesson['title'] ?? '');
        $strand = $meta['strand'] ?? 'unknown';

        $system = <<<'SYS'
        You are an expert primary-mathematics/literacy educator reviewing a lesson for a Standard 4/5 (age 10-12) child in Trinidad & Tobago. Judge it on FIVE things and ground your judgement on reputable sources (NCERT, Illustrative Mathematics, UK NCETM, CCSS) — do not invent maths.

        1) first_principles: does it teach the CONCEPT before the procedure (why, not just a trick)? verdict: sound | shallow | procedure-only.
        2) generality: are rules stated in a general, transferable way, not locked to one case/place? verdict: general | partly | too-specific. If too-specific, say what the general rule should be.
        3) conventions: are conventions (e.g. round-half-up) named as agreed choices, not laws? verdict: ok | states-as-fact | n/a.
        4) visual_model: does a concrete/visual model carry the idea (number line, bar, grid, array, diagram)? verdict: present | text-only. Recommend the ONE widget that best fits this concept from: numberline, fraction-bar, tile-grid, array-dots, clock, bar-chart, tag-in-text, none.
        5) child_safe: any culturally-narrow, religious, violent, or unsafe wording for a young child? List exact words/phrases, or empty if clean.

        Then give the 2-4 most important concrete fixes to bring it to a first-principles, visual, child-safe standard. Respond as JSON:
        {"first_principles":{"verdict":"...","note":"..."},
         "generality":{"verdict":"...","general_rule":"..."},
         "conventions":{"verdict":"...","note":"..."},
         "visual_model":{"verdict":"...","recommended_widget":"..."},
         "child_safe":{"flags":["..."]},
         "fixes":["..."],
         "overall":"one sentence"}
        SYS;

        $user = "OBJECTIVE: {$objective}\nSTRAND: {$strand}\n\nLESSON JSON:\n".json_encode($lesson, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $out = $llm->completeJson($system, $user, 1500);

        return [
            'first_principles' => $out['first_principles'] ?? ['verdict' => 'error'],
            'generality' => $out['generality'] ?? ['verdict' => 'error'],
            'conventions' => $out['conventions'] ?? ['verdict' => 'n/a'],
            'visual_model' => $out['visual_model'] ?? ['verdict' => 'error', 'recommended_widget' => 'none'],
            'child_safe' => $out['child_safe'] ?? ['flags' => []],
            'fixes' => $out['fixes'] ?? [],
            'overall' => $out['overall'] ?? '',
        ];
    }

    /** @param array<int,array<string,mixed>> $summary */
    private function renderTable(array $summary): void
    {
        $rows = [];
        foreach ($summary as $r) {
            $rows[] = [
                $r['module'] ?? '?',
                $r['first_principles']['verdict'] ?? '?',
                $r['generality']['verdict'] ?? '?',
                $r['visual_model']['verdict'] ?? '?',
                $r['visual_model']['recommended_widget'] ?? '?',
                count($r['child_safe']['flags'] ?? []) ? 'FLAG' : '',
            ];
        }
        $this->table(['Module', 'First-principles', 'Generality', 'Visual', 'Widget', 'Safe'], $rows);
    }
}
