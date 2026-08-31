<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * A public blog / resources article (BLOG). Authored as a markdown file with
 * YAML frontmatter in database/data/blog/*.md and imported by ArticleSeeder.
 */
class Article extends Model
{
    protected $fillable = [
        'slug', 'title', 'meta_description', 'excerpt', 'category',
        'keywords', 'author', 'cover', 'read_minutes', 'body_html', 'published_at',
    ];

    protected function casts(): array
    {
        return [
            'keywords' => 'array',
            'published_at' => 'datetime',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * Published articles: those with a past (or present) publish date. Future-dated
     * and null-dated drafts stay hidden from the public blog.
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    public function isPublished(): bool
    {
        return $this->published_at !== null && $this->published_at->lte(now());
    }
}
