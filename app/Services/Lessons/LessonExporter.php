<?php

declare(strict_types=1);

namespace App\Services\Lessons;

use App\Models\Lesson;
use Illuminate\Support\Collection;

/**
 * LessonExporter — dump lessons back to the same JSON bundle format the importer reads (LB-02).
 *
 * Round-trips with LessonImporter: export a lesson (or all of them), edit the JSON, re-import.
 * Also the backup surface — an export is a restore point.
 */
class LessonExporter
{
    public function exportAll(): string
    {
        return $this->encode(
            Lesson::with('module')->get()->map(fn (Lesson $lesson) => $this->toArray($lesson))->all(),
        );
    }

    public function exportLesson(Lesson $lesson): string
    {
        $lesson->loadMissing('module');

        return $this->encode([$this->toArray($lesson)]);
    }

    /**
     * @return array{module:?string, title:string, is_published:bool, blocks:array<int,mixed>}
     */
    private function toArray(Lesson $lesson): array
    {
        return [
            'module' => $lesson->module?->code,
            'title' => (string) $lesson->title,
            'is_published' => (bool) $lesson->is_published,
            'blocks' => is_array($lesson->blocks) ? $lesson->blocks : [],
        ];
    }

    /**
     * @param  Collection<int,mixed>|array<int,mixed>  $data
     */
    private function encode(Collection|array $data): string
    {
        return (string) json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }
}
