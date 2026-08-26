<?php

namespace App\Console\Commands;

use App\Services\LlmService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

/**
 * Applies the granularity audit's #1 finding — undefined vocabulary — by inserting a single
 * additive "Quick words" block near the start of each lesson that names the terms the lesson
 * uses but never defines, in plain Standard-4/5 Trinidad & Tobago language.
 *
 * Additive and idempotent: it never rewrites teaching blocks or answers, and it skips a lesson
 * that already has a glossary block. Run with --dry to preview. Reads the audit reports in
 * storage/app/granularity-audit/ to decide which terms need a gloss.
 */
class LessonsApplyGlossary extends Command
{
    protected $signature = 'lessons:apply-glossary
        {module? : A single lesson module code; omit for all}
        {--dry : Print proposed glosses without writing}';

    protected $description = 'Insert a plain-language "Quick words" block for the audit\'s undefined-vocabulary gaps';

    private const LESSON_DIR = 'database/data/lessons';

    public function handle(LlmService $llm): int
    {
        $only = $this->argument('module');
        $auditDir = storage_path('app/granularity-audit');

        $files = collect(File::files(base_path(self::LESSON_DIR)))
            ->filter(fn ($f) => $f->getExtension() === 'json')
            ->sortBy(fn ($f) => $f->getFilename())->values();

        $patched = 0;
        $skipped = 0;
        foreach ($files as $file) {
            $raw = json_decode(File::get($file->getRealPath()), true);
            $isArr = ! isset($raw['blocks']);
            $lesson = $isArr ? ($raw[0] ?? null) : $raw;
            if (! $lesson) {
                continue;
            }
            $module = $lesson['module'] ?? $file->getFilename();
            if ($only && strcasecmp($module, $only) !== 0) {
                continue;
            }

            // Already glossed? idempotent skip (glossary text is folded into the hook block).
            if (Str::contains($lesson['blocks'][0]['content'] ?? '', 'Quick words:')) {
                $this->line("· {$module} already glossed");
                $skipped++;

                continue;
            }

            $report = $auditDir.'/'.Str::slug($module).'.json';
            if (! File::exists($report)) {
                $this->line("· {$module} no audit report — skipped");
                $skipped++;

                continue;
            }
            $gaps = json_decode(File::get($report), true)['granularity']['gaps'] ?? [];
            $vocabGaps = collect($gaps)
                ->filter(fn ($g) => Str::contains(Str::lower(($g['missing_step'] ?? '').' '.($g['fix'] ?? '')), ['defin', 'meaning', 'vocab', 'what ', 'term']))
                ->pluck('missing_step')->filter()->values()->all();

            if ($vocabGaps === []) {
                $this->line("· {$module} no vocabulary gaps");
                $skipped++;

                continue;
            }

            $glosses = $this->glossFor($llm, $lesson, $vocabGaps);
            if ($glosses === []) {
                $this->line("· {$module} model returned no glosses");
                $skipped++;

                continue;
            }

            $content = 'Quick words: '.collect($glosses)
                ->map(fn ($g) => rtrim($g['term'], '.').' means '.rtrim($g['gloss'], '.').'.')
                ->implode(' ');

            if ($this->option('dry')) {
                $this->info("{$module}:");
                $this->line('  '.$content);

                continue;
            }

            // Fold into the hook block (block 0) so no extra block is added — keeps the
            // 6-10 block authoring rule and puts the vocabulary early where the term appears.
            $lesson['blocks'][0]['content'] = rtrim($lesson['blocks'][0]['content']).' '.$content;
            $out = $isArr ? [$lesson] : $lesson;
            File::put($file->getRealPath(), json_encode($out, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)."\n");
            $this->info("✓ {$module}: ".count($glosses).' words');
            $patched++;
        }

        $this->info(($this->option('dry') ? 'DRY: ' : '')."glossed {$patched}, skipped {$skipped}");

        return self::SUCCESS;
    }

    /**
     * Ask the model for short, accurate, kid-friendly glosses for the flagged terms — grounded on
     * the lesson so a definition matches how the term is used here.
     *
     * @param  array<int,string>  $vocabGaps
     * @return array<int,array{term:string,gloss:string}>
     */
    private function glossFor(LlmService $llm, array $lesson, array $vocabGaps): array
    {
        $blockText = collect($lesson['blocks'])
            ->map(fn ($b) => $b['content'] ?? ($b['question'] ?? ($b['prompt'] ?? ($b['instruction'] ?? ''))))
            ->filter()->implode(' ');

        $system = <<<'SYS'
        You write tiny glossaries for a Standard 4/5 (age 10-12) Trinidad & Tobago learner, often reading on a phone. For each term the lesson USES but does not clearly define, give a gloss of AT MOST 12 plain words — no jargon, concrete, warm. Only include a term if it actually appears in this lesson and a 10-year-old would need it. Skip anything already explained. Return JSON: {"glosses":[{"term":"digit","gloss":"a single number symbol like 0,1,2 up to 9"}]}. Keep it to the 2-5 most important terms.
        SYS;

        $user = 'LESSON TITLE: '.($lesson['title'] ?? '')."\n\nLESSON TEXT:\n".Str::limit($blockText, 1800)
            ."\n\nTERMS THE AUDIT SAYS ARE UNDEFINED:\n- ".implode("\n- ", $vocabGaps);

        $out = $llm->completeJson($system, $user, 700);
        $glosses = $out['glosses'] ?? [];

        return collect($glosses)
            ->filter(fn ($g) => ! empty($g['term']) && ! empty($g['gloss']))
            ->map(fn ($g) => ['term' => (string) $g['term'], 'gloss' => (string) $g['gloss']])
            ->take(5)->values()->all();
    }
}
