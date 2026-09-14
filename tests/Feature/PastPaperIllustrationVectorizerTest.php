<?php

use App\Services\PastPapers\PastPaperIllustrationVectorizer;
use App\Services\PastPapers\PastPaperPageVisionExtractor;
use App\Services\PastPapers\SvgSanitizer;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

it('generates a safe SVG from a rendered source page and attaches it to its question draft', function () {
    Storage::fake('local');
    config()->set('services.llm.key', 'test-key');
    config()->set('services.llm.base_url', 'https://llm.example/v1');
    config()->set('services.llm.past_paper_illustration_model', 'vision/test');
    config()->set('services.llm.past_paper_illustration_fallback_models', []);

    $filename = 'sample.pdf';
    $page = 'past-paper-source/rendered/'.sha1($filename).'/page-001.png';
    Storage::disk('local')->put($page, base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII='));

    $content = json_encode(['illustrations' => [[
            'number' => 7,
            'svg' => '<svg viewBox="0 0 10 10"><circle cx="5" cy="5" r="4"/></svg>',
    ]]]);
    Http::fake(['https://llm.example/v1/chat/completions' => Http::response([
        'choices' => [['message' => ['content' => $content]]],
    ])]);

    $result = app(PastPaperIllustrationVectorizer::class)->vectorise([
        'filename' => $filename,
        'draft' => ['questions' => [['number' => 7, 'prompt' => 'Read the clock.', 'source_page' => 1]]],
    ]);

    expect($result['generated'])->toBe(1)
        ->and(data_get($result, 'draft.draft.questions.0.illustration_svg'))->toContain('<circle');
    Http::assertSent(fn ($request) => $request->url() === 'https://llm.example/v1/chat/completions'
        && $request['messages'][0]['content'][1]['type'] === 'image_url');
});

it('rejects SVG that could execute code or fetch external content', function () {
    expect(SvgSanitizer::clean('<svg viewBox="0 0 1 1"><script>alert(1)</script></svg>'))->toBeNull()
        ->and(SvgSanitizer::clean('<svg viewBox="0 0 1 1"><image href="https://example.com/x.png"/></svg>'))->toBeNull()
        ->and(SvgSanitizer::clean('<svg viewBox="0 0 1 1"><rect width="1" height="1"/></svg>'))->not->toBeNull();
});

it('rebuilds page questions with their printed numbers and source-page SVGs', function () {
    Storage::fake('local');
    config()->set('services.llm.key', 'test-key');
    config()->set('services.llm.base_url', 'https://llm.example/v1');
    config()->set('services.llm.past_paper_illustration_model', 'vision/test');
    config()->set('services.llm.past_paper_illustration_fallback_models', []);

    $filename = 'page-source.pdf';
    Storage::disk('local')->put('past-paper-source/rendered/'.sha1($filename).'/page-002.png', base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII='));
    $content = json_encode(['questions' => [[
        'number' => 8,
        'prompt' => 'State the time on the clock.',
        'options' => [],
        'marks' => 1,
        'illustration_svg' => '<svg viewBox="0 0 10 10"><circle cx="5" cy="5" r="4"/></svg>',
        'confidence' => 0.9,
    ]]]);
    Http::fake(['https://llm.example/v1/chat/completions' => Http::response([
        'choices' => [['message' => ['content' => $content]]],
    ])]);

    $result = app(PastPaperPageVisionExtractor::class)->rebuild(['filename' => $filename, 'draft' => ['questions' => []]]);

    expect($result['questions'])->toBe(1)
        ->and($result['diagrams'])->toBe(1)
        ->and(data_get($result, 'draft.draft.questions.0.number'))->toBe(8)
        ->and(data_get($result, 'draft.draft.questions.0.source_page'))->toBe(2)
        ->and(data_get($result, 'draft.draft.questions.0.illustration_svg'))->toContain('<circle');
});
