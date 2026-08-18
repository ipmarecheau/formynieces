<?php

namespace App\Services\Content;

use App\Models\SyllabusModule;
use Illuminate\Support\Facades\DB;

/**
 * A living audit of authored content vs the minimum the app needs to run
 * seamlessly WITHOUT leaning on realtime AI generation.
 *
 * Behaviour lives in the .feature specs; CONTENT is production status that
 * drifts as it is authored — so it is tracked here (and by `content:coverage`),
 * never as a Gherkin scenario. The `report()` shape is stable so the command,
 * the CONTENT_BACKLOG doc, and (later) the admin Content Audit page can share it.
 *
 * Targets below are the "seamless" bars. They are deliberately simple constants —
 * tune them to your own judgement; the report re-reads them every run.
 */
class ContentCoverageService
{
    /** Practice difficulty rungs (the 1/3/5 level coding). */
    public const RUNGS = [1, 3, 5];

    /** A module is masterable offline only with this many active questions per rung. */
    public const MIN_PER_RUNG = 3;

    /** Reading levels the app is expected to serve (SEA Std-5 band). */
    public const READING_LEVELS = [3, 4, 5, 6, 7];

    /** Unseen passages to stock per level so a term never repeats one (DR-06 / LL-18). */
    public const PASSAGES_PER_LEVEL = 30;

    /** A passage should yield at least this many vocabulary words (DV-01). */
    public const MIN_WORDS_PER_PASSAGE = 3;

    /** Writing genres the Creative Writing paper draws from. */
    public const WRITING_GENRES = ['narrative', 'expository', 'descriptive', 'persuasive'];

    /** One shared prompt per Monday-anchored study week across a ~30-week journey. */
    public const WRITING_WEEKS = 30;

    /**
     * @return array<string, mixed>
     */
    public function report(): array
    {
        return [
            'generated_at' => now()->toDateTimeString(),
            'lessons' => $this->lessons(),
            'practice' => $this->practice(),
            'reading' => $this->reading(),
            'vocabulary' => $this->vocabulary(),
            'writing' => $this->writing(),
        ];
    }

    /**
     * @return array{have:int, need:int, pct:int, missing:array<int, array{code:string, topic:string}>}
     */
    private function lessons(): array
    {
        $need = SyllabusModule::count();

        $haveIds = DB::table('lessons')->where('is_published', true)->pluck('module_id')->unique();

        $missing = SyllabusModule::whereNotIn('id', $haveIds)
            ->orderBy('subject')->orderBy('pacing_week')
            ->get(['code', 'topic'])
            ->map(fn ($m) => ['code' => $m->code, 'topic' => $m->topic])
            ->all();

        $have = $need - count($missing);

        return ['have' => $have, 'need' => $need, 'pct' => $this->pct($have, $need), 'missing' => $missing];
    }

    /**
     * @return array{masterable:int, need:int, pct:int, understocked:array<int, array{code:string, topic:string, rungs:array<int,int>}>}
     */
    private function practice(): array
    {
        $need = SyllabusModule::count();

        // One grouped query: active question counts per module + rung.
        $counts = DB::table('practice_questions')
            ->where('is_active', true)
            ->whereIn('difficulty', self::RUNGS)
            ->select('module_id', 'difficulty', DB::raw('count(*) as c'))
            ->groupBy('module_id', 'difficulty')
            ->get()
            ->groupBy('module_id');

        $understocked = [];
        $masterable = 0;

        foreach (SyllabusModule::orderBy('subject')->orderBy('pacing_week')->get(['id', 'code', 'topic']) as $module) {
            $rungs = [];
            $ok = true;
            foreach (self::RUNGS as $rung) {
                $have = (int) ($counts->get($module->id)?->firstWhere('difficulty', $rung)?->c ?? 0);
                $rungs[$rung] = $have;
                if ($have < self::MIN_PER_RUNG) {
                    $ok = false;
                }
            }

            if ($ok) {
                $masterable++;
            } else {
                $understocked[] = ['code' => $module->code, 'topic' => $module->topic, 'rungs' => $rungs];
            }
        }

        return ['masterable' => $masterable, 'need' => $need, 'pct' => $this->pct($masterable, $need), 'understocked' => $understocked];
    }

    /**
     * @return array{target_per_level:int, per_level:array<int, array{have:int, need:int}>}
     */
    private function reading(): array
    {
        $byLevel = DB::table('reading_passages')->where('is_active', true)
            ->select('reading_level', DB::raw('count(*) as c'))
            ->groupBy('reading_level')->pluck('c', 'reading_level');

        $perLevel = [];
        foreach (self::READING_LEVELS as $level) {
            $have = (int) ($byLevel[$level] ?? 0);
            $perLevel[$level] = ['have' => $have, 'need' => max(0, self::PASSAGES_PER_LEVEL - $have)];
        }

        return ['target_per_level' => self::PASSAGES_PER_LEVEL, 'per_level' => $perLevel];
    }

    /**
     * @return array{words:int, passages:int, thin_passages:int}
     */
    private function vocabulary(): array
    {
        $words = DB::table('vocabulary_words')->count();
        $passages = DB::table('reading_passages')->where('is_active', true)->count();

        $wordsByPassage = DB::table('vocabulary_words')
            ->select('passage_id', DB::raw('count(*) as c'))
            ->groupBy('passage_id')->pluck('c', 'passage_id');

        $thin = DB::table('reading_passages')->where('is_active', true)->pluck('id')
            ->filter(fn ($id) => (int) ($wordsByPassage[$id] ?? 0) < self::MIN_WORDS_PER_PASSAGE)
            ->count();

        return ['words' => $words, 'passages' => $passages, 'thin_passages' => $thin];
    }

    /**
     * @return array{have:int, need:int, pct:int, by_genre:array<string,int>, missing_genres:array<int,string>}
     */
    private function writing(): array
    {
        $have = DB::table('writing_prompts')->count();

        $byGenre = DB::table('writing_prompts')
            ->select('type', DB::raw('count(*) as c'))
            ->groupBy('type')->pluck('c', 'type')->all();

        $missingGenres = array_values(array_diff(self::WRITING_GENRES, array_keys($byGenre)));

        return [
            'have' => $have,
            'need' => self::WRITING_WEEKS,
            'pct' => $this->pct($have, self::WRITING_WEEKS),
            'by_genre' => $byGenre,
            'missing_genres' => $missingGenres,
        ];
    }

    private function pct(int $have, int $need): int
    {
        return $need > 0 ? (int) round($have / $need * 100) : 0;
    }
}
