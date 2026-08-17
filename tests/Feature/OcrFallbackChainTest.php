<?php

use App\Services\SchoolJournal\OcrService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

/** SJ-07 — the OCR seam's vision fallback chain: primary → benchmark-chosen fallbacks. */
it('falls back to the next vision model when the primary fails', function () {
    config()->set('services.llm.key', 'test-key');
    config()->set('services.llm.base_url', 'https://llm.test/v1');
    config()->set('services.llm.vision_model', 'primary/model');
    config()->set('services.llm.vision_fallback_models', ['backup/one', 'backup/two']);

    Storage::fake('local');
    Storage::disk('local')->put('school-journal/fake/paper.jpg', 'not-really-jpeg');

    Http::fake([
        'llm.test/v1/chat/completions' => function ($request) {
            $model = json_decode((string) $request->body(), true)['model'] ?? '';

            if ($model === 'primary/model') {
                return Http::response(['error' => 'overloaded'], 500);
            }

            if ($model === 'backup/one') {
                return Http::response(['choices' => [['message' => ['content' => 'no json here']]]]);
            }

            return Http::response(['choices' => [['message' => ['content' => json_encode([
                'subject' => 'ELA',
                'strand' => 'Grammar',
                'assessment_type' => 'test',
                'score' => '18/20',
                'teacher_comment' => 'Great effort',
                'text' => 'transcription',
                'confidence' => ['score' => 0.5],
            ])]]]]);
        },
    ]);

    $result = app(OcrService::class)->digitize(
        Storage::disk('local')->path('school-journal/fake/paper.jpg'),
        'image/jpeg',
    );

    expect($result)->not->toBeNull()
        ->and($result['fields']['score'])->toBe('18/20')
        ->and($result['review'])->toContain('score');                        // 0.5 < 0.70 → flagged
})->group('scenario:SJ-07');

it('returns null for manual entry when no vision model is configured', function () {
    config()->set('services.llm.vision_model', null);
    config()->set('services.llm.vision_fallback_models', []);

    Storage::fake('local');
    Storage::disk('local')->put('school-journal/fake/paper.jpg', 'x');

    expect(app(OcrService::class)->digitize(
        Storage::disk('local')->path('school-journal/fake/paper.jpg'),
        'image/jpeg',
    ))->toBeNull();
})->group('scenario:SJ-07');
