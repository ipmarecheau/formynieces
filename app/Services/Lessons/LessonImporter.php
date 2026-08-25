<?php

declare(strict_types=1);

namespace App\Services\Lessons;

use App\Models\Lesson;
use App\Models\SyllabusModule;
use App\Support\LessonBlockSchema;

/**
 * LessonImporter — bulk-import lessons from a JSON bundle (LB-01).
 *
 * The bundle is a list of lessons (or a single lesson object), each of the shape:
 *   { "module": "MATH-001", "title": "…", "is_published": true, "blocks": [ … ] }
 *
 * Lessons bind to a module by its stable `code`. Import is an UPSERT by module (one lesson per
 * module), so re-importing the same bundle updates rather than duplicates. Every block is validated
 * against LessonBlockSchema; a lesson with any invalid block (or an unknown module code) is skipped
 * and reported, never half-saved. `preview()` reports what WOULD happen without writing (dry run).
 */
class LessonImporter
{
    /**
     * Dry run: parse + validate + resolve modules, report per-lesson outcome, write nothing.
     *
     * @return array{ok:bool, error?:string, lessons:array<int,array<string,mixed>>, created:int, updated:int, skipped:int}
     */
    public function preview(string $json): array
    {
        return $this->run($json, commit: false);
    }

    /**
     * Import: same validation as preview, then upsert the valid lessons by module.
     *
     * @return array{ok:bool, error?:string, lessons:array<int,array<string,mixed>>, created:int, updated:int, skipped:int}
     */
    public function import(string $json): array
    {
        return $this->run($json, commit: true);
    }

    /**
     * @return array{ok:bool, error?:string, lessons:array<int,array<string,mixed>>, created:int, updated:int, skipped:int}
     */
    private function run(string $json, bool $commit): array
    {
        $decoded = json_decode($json, true);

        if (! is_array($decoded)) {
            return ['ok' => false, 'error' => 'The file is not valid JSON.', 'lessons' => [], 'created' => 0, 'updated' => 0, 'skipped' => 0];
        }

        // Accept a single lesson object as well as a list of them.
        if (array_key_exists('module', $decoded) || array_key_exists('blocks', $decoded)) {
            $decoded = [$decoded];
        }

        $results = [];
        $created = 0;
        $updated = 0;
        $skipped = 0;

        foreach (array_values($decoded) as $i => $entry) {
            $result = $this->analyseEntry($entry, $i + 1);

            if ($result['status'] === 'skip') {
                $skipped++;
                $results[] = $result;

                continue;
            }

            /** @var array<string, mixed> $entry */
            $module = SyllabusModule::where('code', $entry['module'])->first();
            $exists = Lesson::where('module_id', $module->id)->exists();

            if ($commit) {
                Lesson::updateOrCreate(
                    ['module_id' => $module->id],
                    [
                        'title' => $entry['title'],
                        'blocks' => array_values($entry['blocks']),
                        'is_published' => (bool) ($entry['is_published'] ?? true),
                        // Objective codes this lesson teaches directly / reinforces indirectly, for
                        // the objective badge + Syllabus page (optional; defaults to the module's own code).
                        'objectives_direct' => array_values((array) ($entry['objectives_direct'] ?? [$module->code])),
                        'objectives_indirect' => array_values((array) ($entry['objectives_indirect'] ?? [])),
                    ],
                );
            }

            $result['status'] = $exists ? 'update' : 'create';
            $exists ? $updated++ : $created++;
            $results[] = $result;
        }

        return ['ok' => true, 'lessons' => $results, 'created' => $created, 'updated' => $updated, 'skipped' => $skipped];
    }

    /**
     * @return array{position:int, module:?string, title:?string, status:string, errors:array<int,string>}
     */
    private function analyseEntry(mixed $entry, int $position): array
    {
        $errors = [];

        if (! is_array($entry)) {
            return ['position' => $position, 'module' => null, 'title' => null, 'status' => 'skip', 'errors' => ["Lesson #{$position}: is not an object"]];
        }

        $code = $entry['module'] ?? null;
        $title = $entry['title'] ?? null;
        $blocks = $entry['blocks'] ?? null;

        if (! is_string($code) || SyllabusModule::where('code', $code)->doesntExist()) {
            $errors[] = "Lesson #{$position}: no module with code '".(is_string($code) ? $code : 'null')."'";
        }

        if (! is_string($title) || trim($title) === '') {
            $errors[] = "Lesson #{$position}: missing 'title'";
        }

        if (! is_array($blocks) || $blocks === []) {
            $errors[] = "Lesson #{$position}: 'blocks' must be a non-empty list";
        } else {
            foreach (array_values($blocks) as $bi => $block) {
                $errors = array_merge($errors, LessonBlockSchema::validateBlock($block, $bi + 1));
            }
        }

        return [
            'position' => $position,
            'module' => is_string($code) ? $code : null,
            'title' => is_string($title) ? $title : null,
            'status' => $errors === [] ? 'valid' : 'skip',
            'errors' => $errors,
        ];
    }
}
