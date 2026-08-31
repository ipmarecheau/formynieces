<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\get;

uses(RefreshDatabase::class);

it('emits canonical, Open Graph and Twitter tags on the landing page', function () {
    get('/')
        ->assertOk()
        ->assertSee('rel="canonical"', false)
        ->assertSee('property="og:title"', false)
        ->assertSee('property="og:image"', false)
        ->assertSee('name="twitter:card"', false)
        ->assertSee('summary_large_image', false);
})->group('scenario:SEO-01');

it('carries Organization and WebSite structured data on the landing page', function () {
    get('/')
        ->assertOk()
        ->assertSee('application/ld+json', false)
        ->assertSee('"@type":"Organization"', false)
        ->assertSee('"@type":"WebSite"', false)
        ->assertSee('"@type":"EducationalOrganization"', false);
})->group('scenario:SEO-03');

it('gives each public page a unique title and meta description', function () {
    $pages = [
        '/' => 'SmoothSeas — SEA exam prep, sailed with a turtle named Smooth',
        '/about' => 'About SmoothSeas — SEA prep built for Caribbean families',
        '/faq' => 'SmoothSeas FAQ — SEA exam prep questions, answered',
        '/contact' => 'Contact SmoothSeas — SEA prep support for parents',
        '/book-a-call' => 'Book a free SmoothSeas call — SEA prep for your child',
        '/terms' => 'Terms &amp; Conditions — SmoothSeas',
        '/privacy' => 'Privacy Policy — SmoothSeas',
    ];

    foreach ($pages as $path => $title) {
        get($path)
            ->assertOk()
            ->assertSee("<title>{$title}</title>", false)
            ->assertSee('name="description"', false)
            ->assertSee('rel="canonical"', false);
    }
})->group('scenario:SEO-02');

it('serves an XML sitemap listing the public pages', function () {
    get('/sitemap.xml')
        ->assertOk()
        ->assertHeader('Content-Type', 'application/xml')
        ->assertSee('<urlset', false)
        ->assertSee(route('about'), false)
        ->assertSee(route('faq'), false)
        ->assertSee(route('terms'), false)
        ->assertSee(route('privacy'), false);
})->group('scenario:SEO-04');

it('has a robots.txt that allows crawling and points to the sitemap', function () {
    $robots = file_get_contents(public_path('robots.txt'));

    expect($robots)
        ->toContain('Allow: /')
        ->toContain('Sitemap: https://smoothseas.org/sitemap.xml')
        ->toContain('Disallow: /dashboard');
})->group('scenario:SEO-05');
