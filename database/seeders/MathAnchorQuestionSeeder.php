<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Yaml\Yaml;

/**
 * Slice 2b — Math anchor question bank seeder.
 *
 * Loads the authored Math anchors from database/data/math_anchor_bank.yaml into
 * `anchor_questions`, and links each to its target module via the
 * `anchor_question_module` pivot. 65 anchors give >=3x coverage of all 51 Math
 * modules (direct + indirect through the prerequisite graph). The adaptive walk
 * presents only ~15 per child; this is the bank it draws from.
 *
 * PROVENANCE: every anchor is original, authored for this platform and
 * curriculum-aligned to SEA Standard 5 (T&T) — copied from no past paper. The
 * per-item source/license travels with the anchor inside distractor_notes.meta
 * (the schema has no dedicated provenance columns; see NOTE below).
 *
 * NOTE on provenance storage: anchor_questions has no source/license column, so
 * provenance is preserved inside the distractor_notes JSON under a "meta" key.
 * If you later want first-class provenance, add nullable `source` and `license`
 * columns via migration and move the two fields out of meta. Flagged, not assumed.
 *
 * Difficulty mapping: easy=1, medium=2, hard=3 (matches anchor_questions.difficulty int).
 *
 * Depends on SyllabusModuleSeeder (FK: anchor_question_module.module_id -> syllabus_modules.id).
 */
class MathAnchorQuestionSeeder extends Seeder
{
    private const DIFFICULTY_MAP = [
        'easy'   => 1,
        'medium' => 2,
        'hard'   => 3,
    ];

    public function run(): void
    {
        $path = database_path('data/math_anchor_bank.yaml');

        if (! is_file($path)) {
            $this->command?->error("Anchor bank file not found: {$path}");

            return;
        }

        $bank = Yaml::parseFile($path);
        $questions = $bank['questions'] ?? [];

        if ($questions === []) {
            $this->command?->warn('Anchor bank parsed but contained no questions.');

            return;
        }

        // Valid module ids, to fail loudly on a typo'd module reference.
        $validModuleIds = DB::table('syllabus_modules')->pluck('id')->flip();

        $now = now();
        $inserted = 0;

        // Idempotent reseed: clear Math anchors and their pivot rows first. The
        // pivot has ON DELETE CASCADE, but we clear explicitly for clarity.
        $existingMathIds = DB::table('anchor_questions')->where('subject', 'Math')->pluck('id');
        if ($existingMathIds->isNotEmpty()) {
            DB::table('anchor_question_module')->whereIn('anchor_question_id', $existingMathIds)->delete();
            DB::table('anchor_questions')->whereIn('id', $existingMathIds)->delete();
        }

        DB::transaction(function () use ($questions, $validModuleIds, $now, &$inserted) {
            foreach ($questions as $i => $q) {
                $module = $q['module'] ?? null;

                if ($module === null || ! $validModuleIds->has($module)) {
                    throw new \RuntimeException(
                        "Anchor #{$i}: module id " . var_export($module, true) . ' is not a real syllabus_modules.id'
                    );
                }

                $difficultyWord = $q['difficulty'] ?? 'medium';
                $difficulty = self::DIFFICULTY_MAP[$difficultyWord] ?? 2;

                $options = $q['options'] ?? [];
                $correctIndex = $q['correct_index'] ?? null;

                if (count($options) !== 4) {
                    throw new \RuntimeException("Anchor #{$i} (module {$module}): expected 4 options, got " . count($options));
                }
                if (! is_int($correctIndex) || $correctIndex < 0 || $correctIndex > 3) {
                    throw new \RuntimeException("Anchor #{$i} (module {$module}): invalid correct_index");
                }

                // distractor_notes carries the misconception map plus provenance meta.
                $distractorNotes = [
                    'misconceptions' => $q['distractors'] ?? [],
                    'meta' => [
                        'source'  => $q['source'] ?? null,
                        'license' => $q['license'] ?? null,
                    ],
                ];

                $anchorId = DB::table('anchor_questions')->insertGetId([
                    'subject'          => 'Math',
                    'sea_section'      => $q['sea_section'] ?? 'Section I',
                    'strand'           => $q['strand'] ?? null,
                    'difficulty'       => $difficulty,
                    'prompt'           => $q['prompt'],
                    'options'          => json_encode(array_values($options), JSON_UNESCAPED_UNICODE),
                    'correct_index'    => $correctIndex,
                    'distractor_notes' => json_encode($distractorNotes, JSON_UNESCAPED_UNICODE),
                    'is_active'        => true,
                    'created_at'       => $now,
                    'updated_at'       => $now,
                ]);

                DB::table('anchor_question_module')->insert([
                    'anchor_question_id' => $anchorId,
                    'module_id'          => $module,
                    'created_at'         => $now,
                    'updated_at'         => $now,
                ]);

                $inserted++;
            }
        });

        $this->command?->info("Seeded {$inserted} Math anchor questions.");
    }
}