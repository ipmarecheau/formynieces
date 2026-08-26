<?php

namespace App\Console\Commands;

use App\Services\LlmService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

/**
 * Two-agent granularity audit of the lesson bundles.
 *
 * Agent 1 (student simulator) walks each lesson BLIND — answers, rules and
 * re-teach fields stripped — role-playing several personas, and asks the
 * questions a real learner at each level would ask.
 *
 * Agent 2 (curriculum gap analyst) sees the questions, the full lesson, the
 * module's objective and its assumed prerequisites, and decides which questions
 * expose a granularity GAP (a micro-step the lesson skips) versus a legitimate
 * prerequisite or out-of-scope point. It also audits the lesson TITLE.
 *
 * Read-only: writes reports to storage/app/granularity-audit/, never edits lessons.
 */
class LessonsGranularityAudit extends Command
{
    protected $signature = 'lessons:granularity-audit
        {module? : A single lesson module code (e.g. MATH-038); omit to audit all}
        {--fresh : Re-audit even modules that already have a report}';

    protected $description = 'Student-simulator + gap-analyst audit of lesson granularity and titles';

    private const LESSON_DIR = 'database/data/lessons';

    private const OUT_DIR = 'granularity-audit';

    public function handle(LlmService $llm): int
    {
        $catalogue = collect(json_decode(File::get(base_path('database/data/objective_catalogue.json')), true))
            ->keyBy('lesson_code');

        $files = collect(File::files(base_path(self::LESSON_DIR)))
            ->filter(fn ($f) => $f->getExtension() === 'json')
            ->sortBy(fn ($f) => $f->getFilename())
            ->values();

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

            $reportPath = $outDir.'/'.Str::slug($module).'.json';
            if (! $this->option('fresh') && File::exists($reportPath)) {
                $summary[] = json_decode(File::get($reportPath), true);
                $this->line("· {$module} (cached)");

                continue;
            }

            $this->line("→ auditing {$module} …");
            $meta = $catalogue->get($module);
            $report = $this->auditLesson($llm, $lesson, $meta);
            $report['module'] = $module;
            $report['title'] = $lesson['title'] ?? '';
            File::put($reportPath, json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
            $summary[] = $report;
        }

        File::put($outDir.'/_summary.json', json_encode($summary, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
        $this->renderTable($summary);
        $this->info(count($summary).' lessons audited → '.$outDir);

        return self::SUCCESS;
    }

    /** @return array<string,mixed> */
    private function auditLesson(LlmService $llm, array $lesson, ?array $meta): array
    {
        $blind = $this->blindView($lesson);
        $questions = $this->simulateStudents($llm, $blind);
        $analysis = $this->analyseGaps($llm, $lesson, $meta, $questions);
        $analysis['student_questions'] = $questions;

        return $analysis;
    }

    /** Strip answer-revealing fields so the student agent cannot cheat. */
    private function blindView(array $lesson): array
    {
        $blocks = [];
        foreach ($lesson['blocks'] as $i => $b) {
            $clean = ['n' => $i, 'type' => $b['type'] ?? ''];
            foreach (['content', 'question', 'options', 'prompt', 'instruction', 'items', 'steps'] as $keep) {
                if (isset($b[$keep])) {
                    $clean[$keep] = $b[$keep];
                }
            }
            $blocks[] = $clean;
        }

        return ['title' => $lesson['title'] ?? '', 'blocks' => $blocks];
    }

    /** Agent 1: multi-persona student questions in one call. */
    private function simulateStudents(LlmService $llm, array $blind): array
    {
        $personas = config('lesson_audit.personas');
        $personaText = collect($personas)
            ->map(fn ($p) => "- {$p['id']}: {$p['label']}. {$p['profile']}")
            ->implode("\n");

        $system = <<<SYS
        You role-play primary-school students walking through a lesson they are seeing for the FIRST time. You do NOT know the answers — only what is written. For EACH persona, list the genuine questions that student would ask while reading, block by block: undefined words, jumps between steps they cannot follow, prior knowledge the lesson assumes, and anything ambiguous. Ask like a confused child, not a teacher. Give each persona AT MOST 5 of their most important questions, each one short sentence. If the title is confusing or promises something different from the content, add a question with block -1.

        Personas:
        $personaText

        Respond as JSON:
        {"personas":[{"id":"below-level","questions":[{"block":<index or -1 for the title>,"q":"..."}]}, ...]}
        SYS;

        $user = 'LESSON (answers hidden):'."\n".json_encode($blind, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $out = $llm->completeJson($system, $user, 2400);

        return $out['personas'] ?? [];
    }

    /** Agent 2: ground the questions against the objective + prerequisites; audit the title. */
    private function analyseGaps(LlmService $llm, array $lesson, ?array $meta, array $questions): array
    {
        $objective = $meta['objective'] ?? ($lesson['title'] ?? 'unknown');
        $strand = $meta['strand'] ?? 'unknown';
        $code = $meta['code'] ?? 'n/a';
        $priorNote = "Assumed prerequisites: skills from EARLIER modules in the same strand ({$strand}) and general prior-grade number/reading skills. A question that needs only such a prerequisite is NOT a gap.";

        $system = <<<'SYS'
        You are a strict SEA (Trinidad & Tobago primary) curriculum gap analyst. You are given a lesson, the single objective it must teach, its assumed prerequisites, and questions real students asked while reading it blind. Do TWO things.

        1) TITLE AUDIT — judge the lesson's title thoroughly against the content and the objective:
           - accurate (names what the lesson actually teaches),
           - single-skilled (not secretly bundling several sub-skills under one name),
           - clear to a 10-12 year old (no jargon, no vague words),
           - complete (does not over-promise or under-promise the content).
           verdict one of: good | vague | inaccurate | overloaded.

        2) GRANULARITY — for each distinct student question decide:
           - answered (the lesson does address it),
           - prerequisite (needs only an assumed earlier skill — fine),
           - out-of-scope (beyond this objective — fine),
           - GAP (needs a micro-step INSIDE this objective that the lesson skips — a real granularity problem).
           Only GAPs matter. For each GAP give the missing step, where it belongs, a severity (low|med|high), and a one-line fix. Judge overall granularity: granular | minor-gaps | major-gaps.

        Be conservative: do not invent gaps for legitimate prerequisites. Respond as JSON:
        {"title_audit":{"verdict":"...","issue":"...","suggested_title":"..."},
         "granularity":{"verdict":"...","gaps":[{"missing_step":"...","location":"...","severity":"...","fix":"..."}]},
         "overall":"one sentence"}
        SYS;

        $user = "OBJECTIVE ({$code}): {$objective}\nSTRAND: {$strand}\n{$priorNote}\n\n"
            ."FULL LESSON:\n".json_encode($lesson, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)."\n\n"
            ."STUDENT QUESTIONS:\n".json_encode($questions, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        $out = $llm->completeJson($system, $user, 1800);

        return [
            'title_audit' => $out['title_audit'] ?? ['verdict' => 'error', 'issue' => 'no analyst response'],
            'granularity' => $out['granularity'] ?? ['verdict' => 'error', 'gaps' => []],
            'overall' => $out['overall'] ?? '',
        ];
    }

    /** @param array<int,array<string,mixed>> $summary */
    private function renderTable(array $summary): void
    {
        $rows = [];
        foreach ($summary as $r) {
            $gaps = $r['granularity']['gaps'] ?? [];
            $high = collect($gaps)->where('severity', 'high')->count();
            $rows[] = [
                $r['module'] ?? '?',
                Str::limit($r['title'] ?? '', 34),
                $r['title_audit']['verdict'] ?? '?',
                $r['granularity']['verdict'] ?? '?',
                count($gaps).($high ? " ({$high} high)" : ''),
            ];
        }
        $this->table(['Module', 'Title', 'Title verdict', 'Granularity', 'Gaps'], $rows);
    }
}
