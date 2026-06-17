<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Yaml\Yaml;

/**
 * Practice question bank seeder for the learning loop.
 *
 * Loads authored practice questions from database/data/practice_question_bank.yaml
 * into `practice_questions`. Unlike anchors, practice questions link directly to a
 * module via practice_questions.module_id (no pivot), and carry teaching fields
 * (hint, explanation) the diagnostic anchors don't.
 *
 * difficulty: easy=1 (rung 1), medium=2 (rung 2), hard=3 (rung 3). A module needs
 * >=3 distinct questions per rung for the climb to reach mastery.
 *
 * Idempotent: clears practice questions for the seeded module ids before inserting,
 * so re-running db:seed does not duplicate.
 *
 * Depends on SyllabusModuleSeeder (FK: practice_questions.module_id -> syllabus_modules.id).
 */
class PracticeQuestionSeeder extends Seeder
{
    private const DIFFICULTY_MAP = [
        'easy'   => 1,
        'medium' => 2,
        'hard'   => 3,
    ];

    public function run(): void
    {
        $path = database_path('data/practice_question_bank.yaml');

        if (! is_file($path)) {
            $this->command?->error("Practice bank file not found: {$path}");
            return;
        }

        $bank = Yaml::parseFile($path);
        $questions = $bank['questions'] ?? [];

        if ($questions === []) {
            $this->command?->warn('Practice bank parsed but contained no questions.');
            return;
        }

        $validModuleIds = DB::table('syllabus_modules')->pluck('id')->flip();
        $now = now();
        $inserted = 0;

        // Idempotent reseed: clear practice questions for every module this bank touches.
        $touchedModuleIds = collect($questions)->pluck('module')->unique()->values();
        DB::table('practice_questions')->whereIn('module_id', $touchedModuleIds)->delete();

        DB::transaction(function () use ($questions, $validModuleIds, $now, &$inserted) {
            foreach ($questions as $i => $q) {
                $module = $q['module'] ?? null;

                if ($module === null || ! $validModuleIds->has($module)) {
                    throw new \RuntimeException(
                        "Practice #{$i}: module id " . var_export($module, true) . ' is not a real syllabus_modules.id'
                    );
                }

                $difficultyWord = $q['difficulty'] ?? 'easy';
                $difficulty = self::DIFFICULTY_MAP[$difficultyWord] ?? 1;

                $options = $q['options'] ?? [];
                $correctIndex = $q['correct_index'] ?? null;

                if (count($options) !== 4) {
                    throw new \RuntimeException("Practice #{$i} (module {$module}): expected 4 options, got " . count($options));
                }
                if (! is_int($correctIndex) || $correctIndex < 0 || $correctIndex > 3) {
                    throw new \RuntimeException("Practice #{$i} (module {$module}): invalid correct_index");
                }

                DB::table('practice_questions')->insert([
                    'module_id'      => $module,
                    'subject'        => DB::table('syllabus_modules')->where('id', $module)->value('subject'),
                    'sea_section'    => $q['sea_section'] ?? 'Section I',
                    'strand'         => $q['strand'] ?? null,
                    'difficulty'     => $difficulty,
                    'sequence_order' => $q['sequence_order'] ?? null,
                    'prompt'         => $q['prompt'],
                    'options'        => json_encode(array_values($options), JSON_UNESCAPED_UNICODE),
                    'correct_index'  => $correctIndex,
                    'hint'           => $q['hint'] ?? null,
                    'explanation'    => $q['explanation'] ?? null,
                    'is_active'      => true,
                    'created_at'     => $now,
                    'updated_at'     => $now,
                ]);

                $inserted++;
            }
        });

        $this->command?->info("Seeded {$inserted} practice questions.");
    }
}