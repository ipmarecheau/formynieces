<?php

namespace Database\Seeders;

use App\Services\Blog\ArticleImporter;
use Illuminate\Database\Seeder;

/**
 * Seeds version-controlled blog articles from database/data/blog/*.md (BLOG).
 *
 * Each file is markdown with YAML frontmatter; seeding upserts by slug, so
 * re-running is idempotent. Mirrors LessonSeeder.
 */
class ArticleSeeder extends Seeder
{
    public function __construct(private ArticleImporter $importer) {}

    public function run(): void
    {
        $dir = database_path('data/blog');

        if (! is_dir($dir)) {
            return;
        }

        foreach (glob($dir.'/*.md') ?: [] as $file) {
            $this->importer->import((string) file_get_contents($file));
        }
    }
}
