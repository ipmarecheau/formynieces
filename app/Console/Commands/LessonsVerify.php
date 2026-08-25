<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\Lessons\LessonUniqueness;
use Illuminate\Console\Command;

/**
 * lessons:verify — author-time guard that no worked example pre-answers a question.
 *
 * Scans a single bundle (--file) or every bundle in database/data/lessons/ and fails when any
 * lesson reuses its worked-example number in a `fillblank`, `check`, or `practiceItem`
 * (see LessonUniqueness). Run it as the last step of authoring; a clean run is required.
 */
class LessonsVerify extends Command
{
    protected $signature = 'lessons:verify {--file= : Verify a single bundle instead of the whole directory}';

    protected $description = 'Verify lessons never reuse a worked-example number in a question';

    public function handle(LessonUniqueness $uniqueness): int
    {
        $dir = database_path('data/lessons');
        $files = $this->option('file')
            ? [(string) $this->option('file')]
            : (glob($dir.'/*.json') ?: []);

        if ($files === []) {
            $this->warn('No lesson bundles found to verify.');

            return self::SUCCESS;
        }

        $offending = 0;
        foreach ($files as $file) {
            $decoded = json_decode((string) file_get_contents($file), true);
            if (! is_array($decoded)) {
                $this->error(basename($file).': not valid JSON');
                $offending++;

                continue;
            }

            // A bundle is a single lesson object or a list of them.
            $lessons = (array_key_exists('module', $decoded) || array_key_exists('blocks', $decoded))
                ? [$decoded]
                : array_values($decoded);

            foreach ($lessons as $lesson) {
                $blocks = array_values((array) ($lesson['blocks'] ?? []));
                $collisions = $uniqueness->collisions($blocks);
                if ($collisions === []) {
                    continue;
                }

                $offending++;
                $label = $lesson['module'] ?? $lesson['title'] ?? basename($file);
                $this->error(basename($file)." ({$label}) — example number reused in a question:");
                foreach ($collisions as $c) {
                    $this->line("    subject {$c['subject']}: example block {$c['exampleBlock']} → question block {$c['questionBlock']} ({$c['where']})");
                }
            }
        }

        if ($offending > 0) {
            $this->newLine();
            $this->error("{$offending} lesson(s) reuse a worked-example number in a question. Give each question a fresh number.");

            return self::FAILURE;
        }

        $this->info(count($files).' bundle(s) verified — no example/question overlap.');

        return self::SUCCESS;
    }
}
