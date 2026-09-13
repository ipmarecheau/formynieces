<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Route;
use Symfony\Component\Yaml\Yaml;

/**
 * Mobile parity audit — the completeness check for the Flutter apps.
 *
 * Reads formynieces-spec/mobile/parity.yml (one row per web surface) and verifies,
 * for each, that the mobile API route is registered, the Flutter screen file exists,
 * and a Pest test exists. Prints a gap table and (with --write) renders PARITY_MATRIX.md.
 * This is `specs:trace` for the mobile port: it tells us what is genuinely done vs missing.
 */
class MobileParityAudit extends Command
{
    protected $signature = 'mobile:parity-audit {--write : Render formynieces-spec/mobile/PARITY_MATRIX.md} {--only-gaps : Show only rows that are not fully built}';

    protected $description = 'Audit web→mobile feature parity (API route + Flutter screen + test per web surface)';

    public function handle(): int
    {
        $path = base_path('formynieces-spec/mobile/parity.yml');
        if (! is_file($path)) {
            $this->error("Missing {$path}");

            return self::FAILURE;
        }

        $spec = Yaml::parseFile($path);
        $sections = ['child' => 'CHILD APP', 'parent' => 'PARENT APP'];
        $mdSections = [];
        $totals = ['built' => 0, 'partial' => 0, 'missing' => 0, 'all' => 0];

        foreach ($sections as $key => $heading) {
            $rows = [];
            $md = [];
            $sectionBuilt = 0;
            $sectionAll = 0;
            foreach ($spec[$key] ?? [] as $item) {
                $api = $this->check($item['api'] ?? null, fn ($v) => Route::has($v));
                $ui = $this->check($item['flutter'] ?? null, fn ($v) => is_file(base_path('mobile/'.$v)));
                $test = $this->check($item['tests'] ?? null, fn ($v) => is_file(base_path("tests/Feature/Api/{$v}.php")));

                $status = $this->status($api, $ui, $test);
                $totals[$this->bucket($status)]++;
                $totals['all']++;
                $sectionAll++;
                if ($this->bucket($status) === 'built') {
                    $sectionBuilt++;
                }

                $rows[] = [$item['key'], $api['mark'], $ui['mark'], $test['mark'], $status, $this->trim($item['title'] ?? '', 46)];
                $md[] = "| {$item['key']} | {$this->md($api)} | {$this->md($ui)} | {$this->md($test)} | {$status} | ".($item['title'] ?? '').' |';
            }

            if ($this->option('only-gaps')) {
                $rows = array_values(array_filter($rows, fn ($r) => $r[4] !== '✅ built'));
            }

            $this->newLine();
            $this->info($heading);
            $this->table(['key', 'API', 'UI', 'test', 'status', 'title'], $rows);
            // Machine-readable per-section summary (parity-gate.sh keys off this).
            $this->line("SUMMARY {$key}: {$sectionBuilt}/{$sectionAll} built");
            $mdSections[$heading] = $md;
        }

        $this->newLine();
        $this->line("TOTAL: {$totals['built']} built · {$totals['partial']} partial · {$totals['missing']} missing  (of {$totals['all']})");

        if ($this->option('write')) {
            $this->writeMatrix($mdSections, $totals);
        }

        return self::SUCCESS;
    }

    /** @return array{val:?string, ok:bool, mark:string} */
    private function check(?string $value, callable $exists): array
    {
        if ($value === null || $value === '') {
            return ['val' => null, 'ok' => false, 'mark' => '—'];
        }
        $ok = $exists($value);

        return ['val' => $value, 'ok' => $ok, 'mark' => $ok ? '✅' : '❌ MISSING'];
    }

    /** @param array{val:?string,ok:bool} ...$parts */
    private function status(array $api, array $ui, array $test): string
    {
        $declared = array_filter([$api['val'], $ui['val'], $test['val']]);
        if ($declared === []) {
            return '⬜ not started';
        }
        $ok = $api['ok'] && $ui['ok'] && $test['ok'];
        if ($ok) {
            return '✅ built';
        }
        // Declared but something referenced is missing, OR only some layers declared.
        $anyOk = $api['ok'] || $ui['ok'] || $test['ok'];

        return $anyOk ? '🟡 partial' : '❌ broken refs';
    }

    private function bucket(string $status): string
    {
        return match (true) {
            str_contains($status, 'built') => 'built',
            str_contains($status, 'not started') => 'missing',
            default => 'partial',
        };
    }

    /** @param array{val:?string,ok:bool} $c */
    private function md(array $c): string
    {
        return $c['val'] === null ? '—' : ($c['ok'] ? '✅' : '❌');
    }

    private function trim(string $s, int $n): string
    {
        return mb_strlen($s) > $n ? mb_substr($s, 0, $n - 1).'…' : $s;
    }

    /** @param array<string, array<int, string>> $mdSections */
    private function writeMatrix(array $mdSections, array $totals): void
    {
        $out = ['# Mobile Parity Matrix', '', '_Generated by `php artisan mobile:parity-audit --write` on '.now()->toDateString().'. Do not edit by hand — edit `parity.yml`._', ''];
        $out[] = "**{$totals['built']} built · {$totals['partial']} partial · {$totals['missing']} missing** (of {$totals['all']} surfaces)";
        foreach ($mdSections as $heading => $rows) {
            $out[] = '';
            $out[] = "## {$heading}";
            $out[] = '';
            $out[] = '| key | API | UI | test | status | title |';
            $out[] = '|---|---|---|---|---|---|';
            $out = array_merge($out, $rows);
        }
        $file = base_path('formynieces-spec/mobile/PARITY_MATRIX.md');
        file_put_contents($file, implode("\n", $out)."\n");
        $this->info("Wrote {$file}");
    }
}
