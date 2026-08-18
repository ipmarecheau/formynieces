<?php

namespace App\Console\Commands;

use App\Services\Content\ContentCoverageService;
use Illuminate\Console\Command;

/**
 * A living content-coverage report: how much authored content exists vs the
 * minimum the app needs to run without leaning on realtime AI generation.
 *
 * Run `php artisan content:coverage` for the summary, add `--details` to list
 * the actual missing/under-stocked items.
 */
class ContentCoverage extends Command
{
    protected $signature = 'content:coverage {--details : List the missing and under-stocked items}';

    protected $description = 'Report authored content coverage (lessons, practice, reading, vocabulary, writing)';

    public function handle(ContentCoverageService $coverage): int
    {
        $report = $coverage->report();
        $details = (bool) $this->option('details');

        $this->newLine();
        $this->line('  <options=bold>CONTENT COVERAGE — SmoothSeas</>');
        $this->line('  <fg=gray>generated '.$report['generated_at'].'</>');
        $this->newLine();

        $l = $report['lessons'];
        $this->row('Lessons (per topic)', $l['have'], $l['need'], $l['pct']);

        $p = $report['practice'];
        $this->row('Practice — masterable', $p['masterable'], $p['need'], $p['pct'], 'modules with ≥'.ContentCoverageService::MIN_PER_RUNG.' active Qs at each rung 1/3/5');

        $w = $report['writing'];
        $this->row('Writing prompts', $w['have'], $w['need'], $w['pct'], 'one shared prompt per study week');

        $this->newLine();
        $this->line('  <options=bold>Reading passages</> <fg=gray>(target '.$report['reading']['target_per_level'].' per level, to never repeat in a term)</>');
        foreach ($report['reading']['per_level'] as $level => $r) {
            $flag = $r['need'] > 0 ? "<fg=yellow>need {$r['need']} more</>" : '<fg=green>stocked</>';
            $this->line("    level {$level}: {$r['have']} / {$report['reading']['target_per_level']}   {$flag}");
        }

        $v = $report['vocabulary'];
        $this->newLine();
        $this->line("  <options=bold>Vocabulary</>: {$v['words']} words across {$v['passages']} passages"
            .($v['thin_passages'] > 0 ? "   <fg=yellow>{$v['thin_passages']} passage(s) under ".ContentCoverageService::MIN_WORDS_PER_PASSAGE.' words</>' : ''));

        if ($w['missing_genres'] !== []) {
            $this->line('  <fg=yellow>Writing genres not yet covered: '.implode(', ', $w['missing_genres']).'</>');
        }

        if ($details) {
            $this->details($report);
        } else {
            $this->newLine();
            $this->line('  <fg=gray>Run with --details to list missing lessons and under-stocked modules.</>');
        }

        $this->newLine();

        return self::SUCCESS;
    }

    private function row(string $label, int $have, int $need, int $pct, ?string $note = null): void
    {
        $bars = (int) round($pct / 10);
        $bar = str_repeat('▓', $bars).str_repeat('░', 10 - $bars);
        $colour = $pct >= 90 ? 'green' : ($pct >= 50 ? 'yellow' : 'red');
        $gap = $need - $have;
        $gapText = $gap > 0 ? "<fg={$colour}>{$gap} to go</>" : '<fg=green>complete</>';

        $this->line(sprintf('  %-24s <fg=%s>%s</> %3d%%  %d / %d   %s', $label, $colour, $bar, $pct, $have, $need, $gapText));
        if ($note !== null) {
            $this->line("  <fg=gray>".str_repeat(' ', 24).$note.'</>');
        }
    }

    private function details(array $report): void
    {
        $this->newLine();
        $missing = $report['lessons']['missing'];
        if ($missing !== []) {
            $this->line('  <options=bold>Lessons still to author ('.count($missing).')</>');
            foreach ($missing as $m) {
                $this->line("    <fg=gray>{$m['code']}</> {$m['topic']}");
            }
        }

        $understocked = $report['practice']['understocked'];
        if ($understocked !== []) {
            $this->newLine();
            $this->line('  <options=bold>Practice under the masterable floor ('.count($understocked).')</>');
            foreach ($understocked as $u) {
                $r = $u['rungs'];
                $this->line("    <fg=gray>{$u['code']}</> {$u['topic']}  <fg=yellow>[d1:{$r[1]} d3:{$r[3]} d5:{$r[5]}]</>");
            }
        }
    }
}
