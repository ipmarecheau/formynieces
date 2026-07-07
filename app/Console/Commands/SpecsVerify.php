<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;

/**
 * specs:verify — records that you manually checked a scenario in the browser.
 *
 * Usage:
 *   php artisan specs:verify WT-03 --note="Cap override saves and re-renders"
 *
 * What it records, per scenario, into formynieces-spec/verifications.yml:
 *   - the date you checked it
 *   - the snapshot (git commit) you were looking at   <- your "bookmark"
 *   - a fingerprint (hash) of the scenario's text     <- so later drift is detectable
 *   - your note
 *
 * Two guards keep the record honest:
 *   1. Refuses to run if you have uncommitted changes. Otherwise the recorded
 *      snapshot ID would not truthfully describe the code you looked at.
 *   2. Refuses if it can't find the scenario's @scenario:<id> tag in the specs.
 */
class SpecsVerify extends Command
{
    protected $signature = 'specs:verify {scenario : The scenario id, e.g. WT-03}
                                         {--note= : A short note about what you confirmed}';

    protected $description = 'Record a manual browser verification for a Gherkin scenario.';

    /** Where the spec .feature files live (a subfolder of the app). */
    private string $featuresPath;

    /** The ledger file that accumulates verifications. */
    private string $ledgerPath;

    public function handle(): int
    {
        $this->featuresPath = base_path('formynieces-spec/features');
        $this->ledgerPath   = base_path('formynieces-spec/verifications.yml');

        $scenarioId = strtoupper(trim($this->argument('scenario')));
        $note       = (string) ($this->option('note') ?? '');

        // --- Guard 1: working tree must be clean ---------------------------
        // "git status --porcelain" prints one line per changed/untracked file.
        // We allow verifications.yml to be modified or untracked — it is the
        // one file this command itself writes, so blocking on it would prevent
        // running verify commands in sequence without committing between each.
        // Everything else must be clean.
        $status = $this->git(['status', '--porcelain']);
        if ($status === null) {
            $this->error('Could not run git. Is this a git repository?');
            return self::FAILURE;
        }
        $dirtyLines = collect(preg_split('/\R/', trim($status)))
            ->filter(fn (string $line) => $line !== '')
            ->reject(fn (string $line) => str_contains($line, 'verifications.yml'))
            ->values();

        if ($dirtyLines->isNotEmpty()) {
            $this->error('You have uncommitted changes. Commit first, then verify —');
            $this->error('otherwise the recorded snapshot would not match what you looked at.');
            $this->newLine();
            $this->line($dirtyLines->implode("\n"));
            return self::FAILURE;
        }

        // --- Guard 2: the scenario must exist in the specs -----------------
        // We also grab its text so we can fingerprint it.
        $scenarioText = $this->findScenarioText($scenarioId);
        if ($scenarioText === null) {
            $this->error("No scenario tagged @scenario:{$scenarioId} found in:");
            $this->error("  {$this->featuresPath}");
            return self::FAILURE;
        }

        // --- Capture the bookmark: the current snapshot id -----------------
        // "git rev-parse --short HEAD" = "the id of the newest snapshot, short form".
        $commit = trim((string) $this->git(['rev-parse', '--short', 'HEAD']));
        if ($commit === '') {
            $this->error('Could not read the current commit. Have you committed at least once?');
            return self::FAILURE;
        }

        // --- Fingerprint the scenario's text -------------------------------
        // Any change to the scenario body changes this hash. That's how a later
        // run of specs:trace knows the rule moved since you approved it.
        $fingerprint = substr(sha1($this->normalise($scenarioText)), 0, 12);

        // --- Append (or replace) the entry in the ledger -------------------
        $entries = $this->readLedger();

        // Drop any previous entry for this scenario — the newest verification
        // is the one that counts. This is what "moves the bookmark forward".
        $entries = array_values(array_filter(
            $entries,
            fn (array $e) => strtoupper($e['scenario'] ?? '') !== $scenarioId
        ));

        $entries[] = [
            'scenario'    => $scenarioId,
            'verified_at' => date('Y-m-d'),
            'commit'      => $commit,
            'spec_hash'   => $fingerprint,
            'note'        => $note,
        ];

        $this->writeLedger($entries);

        $this->info("Recorded verification for {$scenarioId}");
        $this->line("  snapshot:  {$commit}");
        $this->line("  spec hash: {$fingerprint}");
        if ($note !== '') {
            $this->line("  note:      {$note}");
        }

        return self::SUCCESS;
    }

    /**
     * Find the text of the scenario carrying @scenario:<id>.
     *
     * Dependency-free: we read the .feature files as plain text, locate the
     * tag line, and return from the "Scenario:" line down to the next scenario
     * or the end of the file. No behat/gherkin required.
     */
    private function findScenarioText(string $scenarioId): ?string
    {
        foreach ($this->featureFiles() as $file) {
            $lines = preg_split('/\R/', (string) file_get_contents($file));
            $count = count($lines);

            for ($i = 0; $i < $count; $i++) {
                // Match "@scenario:WT-03" anywhere on a tag line, case-insensitively.
                if (preg_match('/@scenario:' . preg_quote($scenarioId, '/') . '\b/i', $lines[$i])) {
                    // Walk forward to the "Scenario:" line (tags sit above it).
                    $start = $i;
                    while ($start < $count && ! preg_match('/^\s*Scenario\b/i', $lines[$start])) {
                        $start++;
                    }
                    if ($start >= $count) {
                        return trim($lines[$i]); // malformed, but return something
                    }

                    // Collect until the next Scenario/tag block or end of file.
                    $body = [];
                    for ($j = $start; $j < $count; $j++) {
                        if ($j > $start && preg_match('/^\s*(@|Scenario\b)/i', $lines[$j])) {
                            break;
                        }
                        $body[] = $lines[$j];
                    }

                    return implode("\n", $body);
                }
            }
        }

        return null;
    }

    /** @return array<int, string> absolute paths to every .feature file */
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

    /** Collapse whitespace so trivial reformatting doesn't count as a change. */
    private function normalise(string $text): string
    {
        return preg_replace('/\s+/', ' ', trim($text)) ?? $text;
    }

    /**
     * Read verifications.yml into an array of associative rows.
     *
     * Deliberately tiny hand-parser for the exact flat shape we write, so this
     * command has no YAML dependency. Uses Symfony\Yaml only if it's present.
     */
    private function readLedger(): array
    {
        if (! is_file($this->ledgerPath)) {
            return [];
        }

        $raw = (string) file_get_contents($this->ledgerPath);

        if (class_exists(\Symfony\Component\Yaml\Yaml::class)) {
            $parsed = \Symfony\Component\Yaml\Yaml::parse($raw);
            return is_array($parsed) ? $parsed : [];
        }

        // Fallback: parse our own known format (list of "- key: value" blocks).
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

    private function writeLedger(array $entries): void
    {
        $out = "# Manual verification ledger for ForMyNieces.\n"
             . "# Written by `php artisan specs:verify`. Safe to edit by hand.\n\n";

        foreach ($entries as $e) {
            $out .= '- scenario: '    . ($e['scenario'] ?? '') . "\n";
            $out .= '  verified_at: '  . ($e['verified_at'] ?? '') . "\n";
            $out .= '  commit: '       . ($e['commit'] ?? '') . "\n";
            $out .= '  spec_hash: '    . ($e['spec_hash'] ?? '') . "\n";
            $out .= '  note: "'        . str_replace('"', "'", (string) ($e['note'] ?? '')) . "\"\n";
        }

        file_put_contents($this->ledgerPath, $out);
    }

    private function stripQuotes(string $v): string
    {
        $v = trim($v);
        if (strlen($v) >= 2 && ($v[0] === '"' || $v[0] === "'") && $v[strlen($v) - 1] === $v[0]) {
            return substr($v, 1, -1);
        }
        return $v;
    }

    /**
     * Run a git command and return stdout, or null on failure.
     *
     * @param array<int, string> $args
     */
    private function git(array $args): ?string
    {
        $process = new Process(array_merge(['git'], $args), base_path());
        $process->run();

        return $process->isSuccessful() ? $process->getOutput() : null;
    }
}
