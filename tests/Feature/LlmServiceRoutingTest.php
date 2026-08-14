<?php

use App\Services\LlmService;
use Illuminate\Support\Facades\Http;

it('always asks OpenRouter to allow provider fallbacks', function () {
    config([
        'services.llm.key' => 'test-key',
        'services.llm.model' => 'primary/model',
        'services.llm.fallback_models' => [],
        'services.llm.provider_order' => [],
    ]);
    Http::fake(['*/chat/completions' => Http::response(['choices' => [['message' => ['content' => 'ok']]]], 200)]);

    app(LlmService::class)->complete('s', 'u', 64);

    Http::assertSent(function ($request) {
        $body = $request->data();

        return ($body['provider']['allow_fallbacks'] ?? null) === true
            && ! array_key_exists('models', $body)   // no model fallbacks when none configured
            && ! array_key_exists('order', $body['provider']);
    });
});

it('adds configured model fallbacks and provider order to the request', function () {
    config([
        'services.llm.key' => 'test-key',
        'services.llm.model' => 'primary/model',
        'services.llm.fallback_models' => ['back/one', 'back/two'],
        'services.llm.provider_order' => ['Alibaba', 'DeepInfra'],
    ]);
    Http::fake(['*/chat/completions' => Http::response(['choices' => [['message' => ['content' => 'ok']]]], 200)]);

    app(LlmService::class)->complete('s', 'u', 64);

    Http::assertSent(function ($request) {
        $body = $request->data();

        return ($body['models'] ?? null) === ['primary/model', 'back/one', 'back/two']
            && ($body['provider']['order'] ?? null) === ['Alibaba', 'DeepInfra']
            && ($body['provider']['allow_fallbacks'] ?? null) === true;
    });
});

it('caps the models array at 3 items — OpenRouter rejects more', function () {
    config([
        'services.llm.key' => 'test-key',
        'services.llm.model' => 'primary/model',
        // Three fallbacks + the primary would be 4 items; OpenRouter 400s on >3.
        'services.llm.fallback_models' => ['back/one', 'back/two', 'back/three'],
        'services.llm.provider_order' => [],
    ]);
    Http::fake(['*/chat/completions' => Http::response(['choices' => [['message' => ['content' => 'ok']]]], 200)]);

    app(LlmService::class)->complete('s', 'u', 64);

    Http::assertSent(function ($request) {
        $body = $request->data();

        return ($body['models'] ?? null) === ['primary/model', 'back/one', 'back/two']
            && count($body['models']) === 3;
    });
});

it('extracts JSON even when a fallback model wraps it in a code fence or adds prose', function () {
    config(['services.llm.key' => 'test-key']);
    Http::fake(['*/chat/completions' => Http::response([
        'choices' => [['message' => ['content' => "Here you go:\n```json\n{\"content\":3,\"feedback\":\"nice\"}\n```"]]],
    ], 200)]);

    $result = app(LlmService::class)->completeJson('sys', 'user', 256);

    expect($result)->toBe(['content' => 3, 'feedback' => 'nice']);
});
