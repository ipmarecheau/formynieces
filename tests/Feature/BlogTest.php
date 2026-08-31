<?php

use App\Models\Article;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

use function Pest\Laravel\get;

uses(RefreshDatabase::class);

function makeArticle(array $overrides = []): Article
{
    static $n = 0;
    $n++;

    return Article::create(array_merge([
        'slug' => 'article-'.$n,
        'title' => 'Article '.$n.' — SEA prep',
        'meta_description' => 'Meta description for article '.$n.'.',
        'excerpt' => 'Excerpt for article '.$n.'.',
        'category' => 'SEA prep',
        'keywords' => ['sea prep', 'article'],
        'author' => 'SmoothSeas',
        'cover' => null,
        'read_minutes' => 5,
        'body_html' => '<p>Body of article '.$n.'.</p>',
        'published_at' => Carbon::now()->subDays($n),
    ], $overrides));
}

it('lists published articles newest first', function () {
    $older = makeArticle(['title' => 'Older post', 'published_at' => Carbon::now()->subDays(10)]);
    $newer = makeArticle(['title' => 'Newer post', 'published_at' => Carbon::now()->subDay()]);

    $response = get('/blog')->assertOk()->assertSee('Newer post')->assertSee('Older post');

    // Newer appears before Older in the HTML.
    $html = $response->getContent();
    expect(strpos($html, 'Newer post'))->toBeLessThan(strpos($html, 'Older post'));
})->group('scenario:BLOG-01');

it('hides future-dated drafts from the index and 404s their URL', function () {
    makeArticle(['title' => 'Published now', 'published_at' => Carbon::now()->subDay()]);
    $draft = makeArticle(['slug' => 'future-draft', 'title' => 'Future draft', 'published_at' => Carbon::now()->addWeek()]);

    get('/blog')->assertOk()->assertSee('Published now')->assertDontSee('Future draft');
    get('/blog/'.$draft->slug)->assertNotFound();
})->group('scenario:BLOG-02');

it('renders an article page that is search-legible and share-ready', function () {
    $article = makeArticle([
        'slug' => 'how-sea-works',
        'title' => 'How SEA Placement Works',
        'meta_description' => 'A clear guide to SEA placement for parents.',
    ]);

    get('/blog/'.$article->slug)
        ->assertOk()
        ->assertSee('<title>How SEA Placement Works', false)
        ->assertSee('A clear guide to SEA placement for parents.', false)
        ->assertSee('rel="canonical"', false)
        ->assertSee('property="og:title"', false)
        ->assertSee('application/ld+json', false)
        ->assertSee('"Article"', false)
        ->assertSee('datePublished', false);
})->group('scenario:BLOG-03');

it('lists published articles in the sitemap', function () {
    $article = makeArticle(['slug' => 'sitemap-article']);

    get('/sitemap.xml')
        ->assertOk()
        ->assertSee(route('blog.show', $article), false);
})->group('scenario:BLOG-04');

it('filters the index by category', function () {
    makeArticle(['title' => 'Maths post', 'category' => 'SEA prep']);
    makeArticle(['title' => 'AI post', 'category' => 'AI in learning']);

    get('/blog?category='.urlencode('AI in learning'))
        ->assertOk()
        ->assertSee('AI post')
        ->assertDontSee('Maths post');
})->group('scenario:BLOG-05');
