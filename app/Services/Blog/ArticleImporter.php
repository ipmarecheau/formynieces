<?php

namespace App\Services\Blog;

use App\Models\Article;
use RuntimeException;
use Symfony\Component\Yaml\Yaml;

/**
 * Imports a single markdown article file (YAML frontmatter + markdown body) into
 * the articles table, upserting by slug so re-seeding is idempotent. Mirrors the
 * lessons LessonImporter data-file pattern.
 */
class ArticleImporter
{
    public function __construct(private MarkdownConverter $markdown) {}

    public function import(string $contents): Article
    {
        [$frontmatter, $body] = $this->split($contents);

        $data = Yaml::parse($frontmatter);

        if (! is_array($data) || empty($data['slug']) || empty($data['title'])) {
            throw new RuntimeException('Article frontmatter must include at least a slug and title.');
        }

        $attributes = [
            'title' => $data['title'],
            'meta_description' => $data['meta_description'] ?? '',
            'excerpt' => $data['excerpt'] ?? '',
            'category' => $data['category'] ?? 'General',
            'keywords' => $data['keywords'] ?? [],
            'author' => $data['author'] ?? 'SmoothSeas',
            'cover' => $data['cover'] ?? null,
            'read_minutes' => (int) ($data['read_minutes'] ?? $this->estimateReadMinutes($body)),
            'body_html' => $this->markdown->toHtml(trim($body)),
            'published_at' => $data['published_at'] ?? null,
        ];

        return Article::updateOrCreate(['slug' => $data['slug']], $attributes);
    }

    /**
     * @return array{0: string, 1: string} [frontmatter yaml, markdown body]
     */
    private function split(string $contents): array
    {
        $contents = ltrim($contents, "\xEF\xBB\xBF"); // strip a UTF-8 BOM if present.

        if (! preg_match('/^---\s*\n(.*?)\n---\s*\n?(.*)$/s', $contents, $m)) {
            throw new RuntimeException('Article file is missing its YAML frontmatter block.');
        }

        return [$m[1], $m[2]];
    }

    private function estimateReadMinutes(string $body): int
    {
        $words = str_word_count(strip_tags($body));

        return max(1, (int) ceil($words / 220));
    }
}
