<?php

namespace App\Http\Controllers;

use App\Models\Article;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * The public blog / resources library (BLOG): an index of published articles,
 * newest first, with an optional category filter, and the individual article
 * pages. Content is seeded from database/data/blog/*.md.
 */
class BlogController extends Controller
{
    public function index(Request $request): View
    {
        $categories = Article::published()
            ->select('category')
            ->distinct()
            ->orderBy('category')
            ->pluck('category');

        $activeCategory = $request->query('category');

        if ($activeCategory !== null && ! $categories->contains($activeCategory)) {
            $activeCategory = null;
        }

        $articles = Article::published()
            ->when($activeCategory, fn ($query) => $query->where('category', $activeCategory))
            ->orderByDesc('published_at')
            ->get();

        return view('blog.index', [
            'articles' => $articles,
            'categories' => $categories,
            'activeCategory' => $activeCategory,
        ]);
    }

    public function show(Article $article): View
    {
        if (! $article->isPublished()) {
            throw new NotFoundHttpException;
        }

        $related = Article::published()
            ->where('id', '!=', $article->id)
            ->where('category', $article->category)
            ->orderByDesc('published_at')
            ->take(3)
            ->get();

        if ($related->count() < 3) {
            $related = $related->merge(
                Article::published()
                    ->where('id', '!=', $article->id)
                    ->whereNotIn('id', $related->pluck('id'))
                    ->inRandomOrder()
                    ->take(3 - $related->count())
                    ->get()
            );
        }

        return view('blog.show', [
            'article' => $article,
            'related' => $related,
        ]);
    }
}
