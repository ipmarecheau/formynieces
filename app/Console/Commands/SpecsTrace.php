<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;

/**
 * specs:trace — the coverage matrix.
 *
 * For every scenario tagged @scenario:<id> in formynieces-spec/features, it
 * lines up three facts and prints one row:
 *
 *   1. Automated  — is there a Pest test tagged ->group('scenario:<id>')?
 *   2. Manual     — is there an entry in verifications.yml?
 *   3. Fresh?     — has the scenario changed since you last verified it?
 *
 * The "Fresh?" column is the whole point. A scenario is flagged STALE when BOTH:
 *   (a) the scenario's current text no longer matches the fingerprint you saved
 *       when you verified it, AND
 *   (b) some snapshot AFTER your verified snapshot actually touched the spec
 *       file — i.e. the change is newer than your approval, not older.
 *
 * It does NOT and cannot confirm the test asserts what the scenario says.
 * It only guarantees you never silently miss that a rule moved.
 */
class SpecsTrace extends Command
{
    protected $signature = 'specs:trace {--only-problems : Show only rows that need attention}';

    protected $description = 'Reconcile Gherkin scenarios with Pest tests and manual verifications.';

    private string $featuresPath;
    private string $ledgerPath;

    public function handle(): int
    {
        $this->featuresPath = base_path('formynieces-spec/features');
        $this->ledgerPath   = base_path('formynieces-spec/verifications.yml');

        // 1. Every scenario id + its current text, from the .feature files.
        $scenarios = $this->collectScenarios();          // [id => text]
        if ($scenarios === []) {
            $this->warn("No @scenario:<id> tags found in {$this->featuresPath}.");
            return self::SUCCESS;
        }

        // 2. Which scenario ids have a Pest group, and how many tests.
        $groups = $this->collectPestGroups();            // [id => testCount]

        // 3. The verification ledger, keyed by scenario id.
        $verifications = $this->collectVerifications();  // [id => row]

        // Build and print the rows.
        $rows = [];
        foreach ($scenarios as $id => $text) {
            $rows[] = $this->buildRow($id, $text, $groups, $verifications);
        }

        // Orphan Pest groups: a test tagged for a scenario that no longer exists.
        foreach ($groups as $id => $count) {
            if (! isset($scenarios[$id])) {
                $rows[] = [
                    'scenario' => $id,
                    'auto'     => "{$count} test(s)",
                    'manual'   => '—',
                    'status'   => '! orphan test (no such scenario)',
                    'ok'       => false,
                ];
            }
        }

        if ($this->option('only-problems')) {
            $rows = array_values(array_filter($rows, fn ($r) => ! $r['ok']));
            if ($rows === []) {
                $this->info('Everything is linked, verified, and fresh. Nothing to do.');
                return self::SUCCESS;
            }
        }

        $this->table(
            ['Scenario', 'Automated', 'Manual verified', 'Status'],
            array_map(fn ($r) => [$r['scenario'], $r['auto'], $r['manual'], $r['status']], $rows)
        );

        return self::SUCCESS;
    }

    /**
     * Decide the status for one scenario by combining the three facts.
     *
     * @param array<string,int>   $groups
     * @param array<string,array> $verifications
     * @return array{scenario:string,auto:string,manual:string,status:string,ok:bool}
     */
    private function buildRow(string $id, string $text, array $groups, array $verifications): array
    {
        $hasTest = isset($groups[$id]);
        $auto    = $hasTest ? "{$groups[$id]} test(s)" : 'no tests';

        $v = $verifications[$id] ?? null;

        if ($v === null) {
            return [
                'scenario' => $id,
                'auto'     => $auto,
                'manual'   => '—',
                'status'   => $hasTest ? '~ never verified' : 'x untested + unverified',
                'ok'       => false,
            ];
        }

        $manual = ($v['verified_at'] ?? '?') . ' @ ' . ($v['commit'] ?? '?');

        // The two staleness checks.
        $fingerprintChanged = $this->fingerprint($text) !== ($v['spec_hash'] ?? '');
        $specTouchedAfter   = $this->specChangedSince($v['commit'] ?? '');

        if ($fingerprintChanged && $specTouchedAfter) {
            return [
                'scenario' => $id,
                'auto'     => $auto,
                'manual'   => $manual,
                'status'   => '! STALE — spec changed since you verified',
                'ok'       => false,
            ];
        }

        if (! $hasTest) {
            return [
                'scenario' => $id,
                'auto'     => $auto,
                'manual'   => $manual,
                'status'   => '~ verified but no automated test',
                'ok'       => false,
            ];
        }

        return [
            'scenario' => $id,
            'auto'     => $auto,
            'manual'   => $manual,
            'status'   => 'ok current',
            'ok'       => true,
        ];
    }

    /**
     * Did any snapshot AFTER $commit change a spec .feature file?
     *
     * "git log <commit>..HEAD -- <path>" lists only the snapshots between your
     * bookmark and now that touched <path>. Non-empty output = the spec moved
     * forward since your approval. Empty = your approval still covers current specs.
     *
     * We scope to the whole features folder (coarse but safe): any spec edit
     * flags every verification whose fingerprint also changed. The fingerprint
     * check is what keeps it precise — a change to a *different* scenario leaves
     * this scenario's hash intact, so it won't be flagged.
     */
    private function specChangedSince(string $commit): bool
    {
        if ($commit === '') {
            return true; // no bookmark recorded — treat as needing a look
        }

        $out = $this->git([
            'log', "{$commit}..HEAD", '--oneline', '--', 'formynieces-spec/features',
        ]);

        // null (git error) → be conservative and flag it.
        return $out === null ? true : trim($out) !== '';
    }

    /** @return array<string,string> [scenarioId => scenario text] */
    private function collectScenarios(): array
    {
        $scenarios = [];

        foreach ($this->featureFiles() as $file) {
            $lines = preg_split('/\R/', (string) file_get_contents($file));
            $count = count($lines);

            for ($i = 0; $i < $count; $i++) {
                if (preg_match('/@scenario:([A-Za-z0-9\-]+)\b/i', $lines[$i], $m)) {
                    $id = strtoupper($m[1]);

                    // Walk to the Scenario: line, then collect the body.
                    $start = $i;
                    while ($start < $count && ! preg_match('/^\s*Scenario\b/i', $lines[$start])) {
                        $start++;
                    }
                    $body = [];
                    for ($j = $start; $j < $count; $j++) {
                        if ($j > $start && preg_match('/^\s*(@|Scenario\b)/i', $lines[$j])) {
                            break;
                        }
                        if ($j < $count) {
                            $body[] = $lines[$j];
                        }
                    }
                    $scenarios[$id] = implode("\n", $body);
                }
            }
        }

        return $scenarios;
    }

    /**
     * Ask Pest which scenario:* groups exist and how many tests each has.
     *
     * "pest --list-groups" prints lines like "- scenario:WT-03 (3 tests)".
     * We only keep groups whose name starts with "scenario:".
     *
     * @return array<string,int> [scenarioId => testCount]
     */
    private function collectPestGroups(): array
    {
        $process = new Process(['./vendor/bin/pest', '--list-groups'], base_path());
        $process->run();

        // Fall back to artisan test runner name on Windows if needed.
        if (! $process->isSuccessful()) {
            $process = new Process(['vendor\\bin\\pest', '--list-groups'], base_path());
            $process->run();
        }

        $groups = [];
        foreach (preg_split('/\R/', $process->getOutput()) as $line) {
            if (preg_match('/^\s*-\s*scenario:([A-Za-z0-9\-]+)\s*\((\d+)\s+tests?\)/i', $line, $m)) {
                $groups[strtoupper($m[1])] = (int) $m[2];
            }
        }

        return $groups;
    }

    /** @return array<string,array> [scenarioId => ledger row] */
    private function collectVerifications(): array
    {
        if (! is_file($this->ledgerPath)) {
            return [];
        }

        $raw = (string) file_get_contents($this->ledgerPath);

        if (class_exists(\Symfony\Component\Yaml\Yaml::class)) {
            $parsed = \Symfony\Component\Yaml\Yaml::parse($raw);
            $rows = is_array($parsed) ? $parsed : [];
        } else {
            $rows = $this->parseLedgerFallback($raw);
        }

        $keyed = [];
        foreach ($rows as $row) {
            if (isset($row['scenario'])) {
                $keyed[strtoupper($row['scenario'])] = $row;
            }
        }

        return $keyed;
    }

    private function parseLedgerFallback(string $raw): array
    {
        $entries = [];
        $current = null;
        foreach (preg_split('/\R/', $raw) as $line) {
            if (preg_match('/^\s*-\s+(\w+):\s*(.*)$/', $line, $m)) {
                if ($current !== null) {
                    $entries[] = $current;
                }
                $current = [$m[1] => $this->stripQuotes($m[2])];
            } elseif (preg_match('/^\s+(\w+):\s*(.*)$/', $line, $m) && $current !== null) {
                $current[$m[1]] = $this->stripQuotes($m[2]);
            }
        }
        if ($current !== null) {
            $entries[] = $current;
        }

        return $entries;
    }

    private function fingerprint(string $text): string
    {
        return substr(sha1(preg_replace('/\s+/', ' ', trim($text)) ?? $text), 0, 12);
    }

    /** @return array<int,string> */
    private function featureFiles(): array
    {
        if (! is_dir($this->featuresPath)) {
            return [];
        }

        $found = [];
        $it = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($this->featuresPath, \FilesystemIterator::SKIP_DOTS)
        );
        foreach ($it as $entry) {
            if ($entry->isFile() && str_ends_with($entry->getFilename(), '.feature')) {
                $found[] = $entry->getPathname();
            }
        }

        return $found;
    }

    private function stripQuotes(string $v): string
    {
        $v = trim($v);
        if (strlen($v) >= 2 && ($v[0] === '"' || $v[0] === "'") && $v[strlen($v) - 1] === $v[0]) {
            return substr($v, 1, -1);
        }
        return $v;
    }

    /** @param array<int,string> $args */
    private function git(array $args): ?string
    {
        $process = new Process(array_merge(['git'], $args), base_path());
        $process->run();

        return $process->isSuccessful() ? $process->getOutput() : null;
    }
}
