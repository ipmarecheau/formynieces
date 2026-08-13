<?php

namespace Database\Seeders;

use App\Services\Lessons\LessonImporter;
use Illuminate\Database\Seeder;

/**
 * Seeds version-controlled lessons from database/data/lessons/*.json (LB-03).
 *
 * Each file is a JSON lesson bundle in the importer's format; seeding upserts by module code, so
 * re-running is idempotent. Depends on SyllabusModuleSeeder (module codes must exist first).
 */
class LessonSeeder extends Seeder
{
    public function __construct(private LessonImporter $importer) {}

    public function run(): void
    {
        $dir = database_path('data/lessons');

        if (! is_dir($dir)) {
            return;
        }

        foreach (glob($dir.'/*.json') ?: [] as $file) {
            $this->importer->import((string) file_get_contents($file));
        }
    }
}
