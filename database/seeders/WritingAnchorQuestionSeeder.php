<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Yaml\Yaml;

/**
 * Slice 2c — Writing anchor question bank seeder.
 *
 * Loads authored Writing anchors from database/data/writing_anchor_bank.yaml into anchor_questions,
 * linking each to its target module via anchor_question_module. Mirrors
 * MathAnchorQuestionSeeder exactly; only the subject label and source file differ.
 *
 * PROVENANCE carried in distractor_notes.meta (no dedicated columns; see Math seeder note).
 * Difficulty: easy=1, medium=2, hard=3. Depends on SyllabusModuleSeeder.
 */
class WritingAnchorQuestionSeeder extends Seeder
{
    private const SUBJECT = 'Writing';
    private const FILE = 'writing_anchor_bank.yaml';

    private const DIFFICULTY_MAP = ['easy' => 1, 'medium' => 2, 'hard' => 3];

    public function run(): void
    {
        $path = database_path('data/' . self::FILE);

        if (! is_file($path)) {
            $this->command?->error("Anchor bank file not found: {$path}");

            return;
        }

        $questions = Yaml::parseFile($path)['questions'] ?? [];

        if ($questions === []) {
            $this->command?->warn('Anchor bank parsed but contained no questions.');

            return;
        }

        $validModuleIds = DB::table('syllabus_modules')->pluck('id')->flip();
        $now = now();
        $inserted = 0;

        $existing = DB::table('anchor_questions')->where('subject', self::SUBJECT)->pluck('id');
        if ($existing->isNotEmpty()) {
            DB::table('anchor_question_module')->whereIn('anchor_question_id', $existing)->delete();
            DB::table('anchor_questions')->whereIn('id', $existing)->delete();
        }

        DB::transaction(function () use ($questions, $validModuleIds, $now, &$inserted) {
            foreach ($questions as $i => $q) {
                $module = $q['module'] ?? null;

                if ($module === null || ! $validModuleIds->has($module)) {
                    throw new \RuntimeException("Anchor #{$i}: module id " . var_export($module, true) . ' is not a real syllabus_modules.id');
                }

                $difficulty = self::DIFFICULTY_MAP[$q['difficulty'] ?? 'medium'] ?? 2;
                $options = $q['options'] ?? [];
                $correctIndex = $q['correct_index'] ?? null;

                if (count($options) !== 4) {
                    throw new \RuntimeException("Anchor #{$i} (module {$module}): expected 4 options, got " . count($options));
                }
                if (! is_int($correctIndex) || $correctIndex < 0 || $correctIndex > 3) {
                    throw new \RuntimeException("Anchor #{$i} (module {$module}): invalid correct_index");
                }

                $distractorNotes = [
                    'misconceptions' => $q['distractors'] ?? [],
                    'meta' => ['source' => $q['source'] ?? null, 'license' => $q['license'] ?? null],
                ];

                $anchorId = DB::table('anchor_questions')->insertGetId([
                    'subject'          => self::SUBJECT,
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

        $this->command?->info("Seeded {$inserted} " . self::SUBJECT . ' anchor questions.');
    }
}
