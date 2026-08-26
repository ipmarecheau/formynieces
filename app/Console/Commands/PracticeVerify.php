<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\PracticeQuestion;
use Illuminate\Console\Command;
use Symfony\Component\Yaml\Yaml;

/**
 * practice:verify — integrity + coverage guard for the practice question bank.
 *
 * Checks database/data/practice_question_bank.yaml: every question is well-formed (exactly four
 * options, an in-range correct_index, a real module, no metadata leaking into the explanation, no
 * duplicate prompt within a module), and every module reaches the 15-per-level floor at D1/D3/D5.
 * Reports the modules still short so the backfill has a live worklist.
 */
class PracticeVerify extends Command
{
    protected $signature = 'practice:verify {--min=15 : Minimum questions required per level}';

    protected $description = 'Verify the practice bank is well-formed and stocked to the per-level minimum';

    private const DIFFICULTY_MAP = ['easy' => 1, 'medium' => 3, 'hard' => 5];

    public function handle(): int
    {
        $min = (int) $this->option('min');
        $path = database_path('data/practice_question_bank.yaml');

        if (! is_file($path)) {
            $this->error("Practice bank file not found: {$path}");

            return self::FAILURE;
        }

        $questions = Yaml::parseFile($path)['questions'] ?? [];
        $validModuleIds = \DB::table('syllabus_modules')->pluck('id')->flip();

        $errors = [];
        $counts = [];   // [module][difficulty] => n
        $seenPrompts = []; // [module] => [normalizedPrompt => true]

        foreach ($questions as $i => $q) {
            $module = $q['module'] ?? null;
            $diffWord = $q['difficulty'] ?? 'easy';
            $difficulty = self::DIFFICULTY_MAP[$diffWord] ?? null;
            $options = $q['options'] ?? [];
            $correct = $q['correct_index'] ?? null;
            $explanation = (string) ($q['explanation'] ?? '');
            $prompt = (string) ($q['prompt'] ?? '');

            if ($module === null || ! $validModuleIds->has($module)) {
                $errors[] = "#{$i}: module ".var_export($module, true).' is not a real module';

                continue;
            }
            if ($difficulty === null) {
                $errors[] = "#{$i} (module {$module}): unknown difficulty '{$diffWord}'";
            }
            if (count($options) !== 4) {
                $errors[] = "#{$i} (module {$module}): needs exactly 4 options, has ".count($options);
            }
            if (! is_int($correct) || $correct < 0 || $correct > 3) {
                $errors[] = "#{$i} (module {$module}): correct_index {$correct} out of range 0-3";
            }
            if (trim($prompt) === '') {
                $errors[] = "#{$i} (module {$module}): empty prompt";
            }
            if (preg_match('/Topic:|Difficulty:/i', $explanation)) {
                $errors[] = "#{$i} (module {$module}): explanation leaks Topic:/Difficulty: metadata";
            }

            // A question's identity is its prompt AND its option set (the app's content hash).
            // Two MC items may share a generic stem ("Which word is spelt correctly?") yet be
            // distinct questions via different options — only an identical prompt+options is a dup.
            $key = PracticeQuestion::hashFor($prompt, is_array($options) ? $options : []);
            if (trim($prompt) !== '' && isset($seenPrompts[$module][$key])) {
                $errors[] = "#{$i} (module {$module}): duplicate question (same prompt + options) within the module";
            }
            $seenPrompts[$module][$key] = true;

            if ($difficulty !== null) {
                $counts[$module][$difficulty] = ($counts[$module][$difficulty] ?? 0) + 1;
            }
        }

        // Coverage: every module must reach $min at each of D1/D3/D5.
        $short = [];
        foreach ($validModuleIds->keys() as $moduleId) {
            foreach ([1, 3, 5] as $d) {
                $have = $counts[$moduleId][$d] ?? 0;
                if ($have < $min) {
                    $short[$moduleId][$d] = $have;
                }
            }
        }

        foreach ($errors as $e) {
            $this->error($e);
        }

        if ($short !== []) {
            $this->warn(count($short).' module(s) below the '.$min.'-per-level floor:');
            foreach ($short as $moduleId => $levels) {
                $parts = [];
                foreach ($levels as $d => $have) {
                    $parts[] = "D{$d}={$have}";
                }
                $this->line("    module {$moduleId}: ".implode(' ', $parts));
            }
        }

        if ($errors !== [] || $short !== []) {
            $this->newLine();
            $this->error('Practice bank not yet complete: '.count($errors).' integrity error(s), '.count($short).' module(s) understocked.');

            return self::FAILURE;
        }

        $this->info('Practice bank verified — all modules stocked to '.$min.'/level, no integrity errors.');

        return self::SUCCESS;
    }
}
