<?php

namespace Database\Seeders;

use App\Models\PracticeQuestion;
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
 * difficulty: easy=1, medium=3, hard=5 — the loop's real climb rungs (1 -> 3 -> 5,
 * mastery at 5; see RecordPracticeAttempt/CompetencyCheck). A module needs >=3
 * distinct questions per rung for the climb to reach mastery.
 *
 * NON-DESTRUCTIVE upsert keyed on content hash (prompt + option set): re-running db:seed
 * never deletes existing questions (a separately-imported bank survives) and never duplicates
 * the yaml's own content. See PracticeQuestionSeederTest.
 *
 * Depends on SyllabusModuleSeeder (FK: practice_questions.module_id -> syllabus_modules.id).
 */
class PracticeQuestionSeeder extends Seeder
{
    private const DIFFICULTY_MAP = [
        'easy' => 1,
        'medium' => 3,
        'hard' => 5,
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
        $subjectFor = DB::table('syllabus_modules')->pluck('subject', 'id');
        $upserted = 0;

        // Non-destructive: upsert by content hash (prompt + option set). Re-seeding never deletes
        // existing questions — the Moodle-imported bank and the authored bank both survive — and a
        // question already present is updated in place rather than duplicated.
        DB::transaction(function () use ($questions, $validModuleIds, $subjectFor, &$upserted) {
            foreach ($questions as $i => $q) {
                $module = $q['module'] ?? null;

                if ($module === null || ! $validModuleIds->has($module)) {
                    throw new \RuntimeException(
                        "Practice #{$i}: module id ".var_export($module, true).' is not a real syllabus_modules.id'
                    );
                }

                $difficultyWord = $q['difficulty'] ?? 'easy';
                $difficulty = self::DIFFICULTY_MAP[$difficultyWord] ?? 1;

                $options = array_values($q['options'] ?? []);
                $correctIndex = $q['correct_index'] ?? null;

                if (count($options) !== 4) {
                    throw new \RuntimeException("Practice #{$i} (module {$module}): expected 4 options, got ".count($options));
                }
                if (! is_int($correctIndex) || $correctIndex < 0 || $correctIndex > 3) {
                    throw new \RuntimeException("Practice #{$i} (module {$module}): invalid correct_index");
                }

                PracticeQuestion::updateOrCreate(
                    ['content_hash' => PracticeQuestion::hashFor((string) $q['prompt'], $options)],
                    [
                        'module_id' => $module,
                        'subject' => $subjectFor[$module] ?? null,
                        'sea_section' => $q['sea_section'] ?? 'Section I',
                        'strand' => $q['strand'] ?? null,
                        'difficulty' => $difficulty,
                        'sequence_order' => $q['sequence_order'] ?? null,
                        'prompt' => $q['prompt'],
                        'options' => $options,
                        'correct_index' => $correctIndex,
                        'hint' => $q['hint'] ?? null,
                        'explanation' => $q['explanation'] ?? null,
                        'is_active' => true,
                    ],
                );

                $upserted++;
            }
        });

        $this->command?->info("Upserted {$upserted} practice questions (non-destructive).");
    }
}
