<?php

namespace App\Console\Commands;

use Behat\Gherkin\Keywords\ArrayKeywords;
use Behat\Gherkin\Lexer;
use Behat\Gherkin\Parser;
use Illuminate\Console\Command;
use Symfony\Component\Process\Process;

/**
 * specs:trace — reconciles Gherkin scenarios with Pest tests.
 *
 * Source of truth, two sides:
 *   - Scenarios in formynieces-spec/features, each carrying a @scenario:<id> tag.
 *   - Pest tests tagged ->group('scenario:<id>').
 *
 * Reports three deltas:
 *   1. Scenarios with NO matching Pest group  → your build queue.
 *   2. Pest groups with NO matching scenario  → orphan tests (renamed/deleted scenario).
 *   3. Scenarios tagged @pending that DO have a Pest group → flip the tag, it's built.
 *
 * It does NOT verify the test asserts what the scenario says — only structural linkage.
 */
class SpecsTrace extends Command
{
    protected $signature = 'specs:trace';
    protected $description = 'Reconcile Gherkin scenarios with Pest test groups and report deltas.';

    public function handle(): int
    {
        $featuresDir = base_path('formynieces-spec/features');

        if (! is_dir($featuresDir)) {
            $this->error("Features directory not found: {$featuresDir}");
            return self::FAILURE;
        }

        $scenarios = $this->parseScenarios($featuresDir);   // [id => ['name'=>, 'pending'=>bool, 'feature'=>]]
        $pestGroups = $this->pestScenarioGroups();          // [id, id, ...]

        $scenarioIds = array_keys($scenarios);

        $missingTests = array_values(array_filter(
            $scenarioIds,
            fn ($id) => ! in_array($id, $pestGroups, true)
        ));

        $orphanTests = array_values(array_filter(
            $pestGroups,
            fn ($id) => ! in_array($id, $scenarioIds, true)
        ));

        $mistaggedPending = array_values(array_filter(
            $scenarioIds,
            fn ($id) => $scenarios[$id]['pending'] && in_array($id, $pestGroups, true)
        ));

        $this->section('Scenarios with NO test (build queue)', $missingTests, $scenarios);
        $this->section('Orphan tests (group has no scenario)', $orphanTests, $scenarios, isOrphan: true);
        $this->section('Built but still tagged @pending (flip the tag)', $mistaggedPending, $scenarios);

        $clean = ! $missingTests && ! $orphanTests && ! $mistaggedPending;
        $this->newLine();
        $this->line($clean
            ? '<info>✓ Specs and tests are in sync.</info>'
            : '<comment>Deltas found — reconcile feature files or tests above.</comment>');

        // Non-zero exit on deltas so this can gate CI later.
        return $clean ? self::SUCCESS : self::FAILURE;
    }

    /** Parse every .feature file, extracting @scenario:<id> tags and @pending state. */
    private function parseScenarios(string $dir): array
    {
        $keywords = new ArrayKeywords([
            'en' => [
                'feature' => 'Feature', 'background' => 'Background',
                'scenario' => 'Scenario', 'scenario_outline' => 'Scenario Outline|Scenario Template',
                'examples' => 'Examples|Scenarios',
                'given' => 'Given', 'when' => 'When', 'then' => 'Then',
                'and' => 'And', 'but' => 'But',
            ],
        ]);
        $parser = new Parser(new Lexer($keywords));

        $out = [];
        foreach (glob("{$dir}/*.feature") as $file) {
            $feature = $parser->parse(file_get_contents($file));
            if ($feature === null) {
                continue;
            }
            foreach ($feature->getScenarios() as $scenario) {
                $tags = $scenario->getTags();
                $id = null;
                foreach ($tags as $tag) {
                    if (str_starts_with($tag, 'scenario:')) {
                        $id = substr($tag, strlen('scenario:'));
                        break;
                    }
                }
                if ($id === null) {
                    // Scenario without a @scenario:<id> tag — surface it as untracked.
                    $id = '(untagged) ' . $scenario->getTitle();
                }
                $out[$id] = [
                    'name'    => $scenario->getTitle(),
                    'pending' => in_array('pending', $tags, true),
                    'feature' => basename($file),
                ];
            }
        }
        return $out;
    }

    /** Ask Pest for all group names, keep the scenario:<id> ones, return the ids. */
    private function pestScenarioGroups(): array
    {
        $process = new Process(['php', 'vendor/bin/pest', '--list-groups'], base_path());
        $process->run();
        $output = $process->getOutput() . $process->getErrorOutput();

        $ids = [];
        foreach (preg_split('/\r?\n/', $output) as $line) {
            // --list-groups prints group names, often as "#group" or "- group".
            if (preg_match('/scenario:([A-Za-z0-9\-_]+)/', $line, $m)) {
                $ids[] = $m[1];
            }
        }
        return array_values(array_unique($ids));
    }

    private function section(string $heading, array $ids, array $scenarios, bool $isOrphan = false): void
    {
        $this->newLine();
        $this->line("<options=bold>{$heading}</> (" . count($ids) . ')');
        if (! $ids) {
            $this->line('  — none');
            return;
        }
        foreach ($ids as $id) {
            if ($isOrphan) {
                $this->line("  • scenario:{$id}  (no matching scenario in features)");
            } else {
                $s = $scenarios[$id];
                $tag = $s['pending'] ? ' [@pending]' : '';
                $this->line("  • {$id}{$tag}  —  {$s['name']}  ({$s['feature']})");
            }
        }
    }
}