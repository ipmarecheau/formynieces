<?php

namespace App\Services\UserTesting;

use App\Models\VerificationRun;
use Illuminate\Support\Facades\Process;

/**
 * Explains, from version control, how a scenario changed since a tester gave their verdict:
 * what the Gherkin diff was and which implementation commits landed. The fingerprints decide
 * *that* something changed (see ScenarioFingerprint); this says *what* and *why*, straight from
 * git, so the re-test email and UI can be specific.
 */
class ScenarioChangeLog
{
    /** The current HEAD commit, or null outside a git checkout. */
    public function currentCommit(): ?string
    {
        $result = Process::path(base_path())->run('git rev-parse HEAD');

        return $result->successful() ? trim($result->output()) : null;
    }

    /**
     * A short, human explanation of what changed for a waiting/answered run since it was given,
     * drawn from the implementation commits and whether the Gherkin block moved.
     *
     * @return array{spec: bool, impl: bool, commits: array<int, string>}
     */
    public function summarise(VerificationRun $run, bool $specChanged, bool $implChanged): array
    {
        $commits = [];

        if ($implChanged && $run->verified_commit) {
            $sources = TestingCatalog::sources($run->scenario_id);

            if ($sources !== []) {
                $result = Process::path(base_path())->run(array_merge(
                    ['git', 'log', '--no-merges', '--pretty=%s', $run->verified_commit.'..HEAD', '--'],
                    $sources,
                ));

                if ($result->successful()) {
                    $commits = array_values(array_filter(array_map('trim', explode("\n", $result->output()))));
                }
            }
        }

        return [
            'spec' => $specChanged,
            'impl' => $implChanged,
            'commits' => array_slice($commits, 0, 8),
        ];
    }
}
