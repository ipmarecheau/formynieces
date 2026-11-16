<?php

namespace App\Services\UserTesting;

/**
 * Fingerprints a scenario so a tester's verdict can tell when the thing they tested has since
 * changed. Two independent hashes, at per-scenario granularity:
 *
 *  - specHash: the scenario's Gherkin block in formynieces-spec/features/*.feature, normalised
 *    and hashed exactly as `specs:trace` / `specs:verify` do, so the two pipelines agree.
 *  - implHash: the files that implement that specific scenario (its catalog `sources`), hashed
 *    by content — so it changes the moment the implementation does, even before a commit.
 *
 * Results are memoised for the request; the git-backed explanations live in ScenarioChangeLog.
 */
class ScenarioFingerprint
{
    /** @var array<string, string>|null [scenarioId => gherkin block] */
    private ?array $scenarios = null;

    /** @var array<string, ?string> */
    private array $implCache = [];

    /**
     * The 12-char fingerprint of a scenario's Gherkin block, or null if the scenario has no
     * backing .feature block (e.g. a tool-only scenario).
     */
    public function specHash(string $scenarioId): ?string
    {
        $text = $this->scenarioBlocks()[strtoupper($scenarioId)] ?? null;

        return $text === null ? null : $this->fingerprint($text);
    }

    /**
     * The 12-char fingerprint of the files that implement a scenario. Missing files are skipped;
     * returns null when none of the declared sources exist (nothing to compare against).
     *
     * @param  array<int, string>  $sources  repo-relative paths
     */
    public function implHash(array $sources): ?string
    {
        if ($sources === []) {
            return null;
        }

        $key = implode('|', $sources);

        return $this->implCache[$key] ??= $this->hashSources($sources);
    }

    /**
     * @param  array<int, string>  $sources
     */
    private function hashSources(array $sources): ?string
    {
        $parts = [];

        foreach ($sources as $relative) {
            $path = base_path($relative);
            if (is_file($path)) {
                // Prefix the path so a line moving between files still changes the hash.
                $parts[] = $relative."\0".(string) file_get_contents($path);
            }
        }

        return $parts === [] ? null : substr(sha1(implode("\0", $parts)), 0, 12);
    }

    /** Normalise + hash a Gherkin block, identical to the specs pipeline. */
    private function fingerprint(string $text): string
    {
        return substr(sha1(preg_replace('/\s+/', ' ', trim($text)) ?? $text), 0, 12);
    }

    /**
     * Parse every @scenario:<id> block out of the feature files, once.
     *
     * @return array<string, string>
     */
    private function scenarioBlocks(): array
    {
        if ($this->scenarios !== null) {
            return $this->scenarios;
        }

        $this->scenarios = [];
        $dir = base_path('formynieces-spec/features');

        if (! is_dir($dir)) {
            return $this->scenarios;
        }

        foreach (glob($dir.'/*.feature') ?: [] as $file) {
            $lines = preg_split('/\R/', (string) file_get_contents($file)) ?: [];
            $count = count($lines);

            for ($i = 0; $i < $count; $i++) {
                if (! preg_match('/@scenario:([A-Za-z0-9\-]+)\b/i', $lines[$i], $m)) {
                    continue;
                }

                $id = strtoupper($m[1]);

                // Walk to the Scenario: line, then collect its body until the next tag/Scenario.
                $start = $i;
                while ($start < $count && ! preg_match('/^\s*Scenario\b/i', $lines[$start])) {
                    $start++;
                }

                $body = [];
                for ($j = $start; $j < $count; $j++) {
                    if ($j > $start && preg_match('/^\s*(@|Scenario\b)/i', $lines[$j])) {
                        break;
                    }
                    $body[] = $lines[$j];
                }

                $this->scenarios[$id] = implode("\n", $body);
            }
        }

        return $this->scenarios;
    }
}
