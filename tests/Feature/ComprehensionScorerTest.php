<?php

use App\Models\ReadingPassage;
use App\Services\LlmService;
use App\Services\Reading\ComprehensionScorer;

function csPassage(): ReadingPassage
{
    return ReadingPassage::create([
        'title' => 'P', 'body' => 'body', 'reading_level' => 5, 'word_count' => 50,
        'questions' => [
            ['prompt' => 'Q1', 'type' => 'mc', 'options' => ['a', 'b'], 'correct_index' => 0],
            ['prompt' => 'Q2', 'type' => 'mc', 'options' => ['a', 'b'], 'correct_index' => 1],
        ],
        'is_active' => true,
    ]);
}

it('uses the LLM score and feedback when available', function () {
    $this->mock(LlmService::class, fn ($m) => $m->shouldReceive('completeJson')
        ->andReturn(['score' => 88, 'feedback' => 'Great thinking about the lighthouse!']));

    $passage = csPassage();
    // Both MC answers wrong (baseline would be 0) — proves the LLM value is used.
    $result = app(ComprehensionScorer::class)->score($passage, $passage->questions, [0 => 1, 1 => 0]);

    expect($result['score'])->toBe(88)
        ->and($result['feedback'])->toBe('Great thinking about the lighthouse!');
})->group('scenario:DR-07');

it('falls back to the multiple-choice auto-grade when the LLM is unavailable', function () {
    $this->mock(LlmService::class, fn ($m) => $m->shouldReceive('completeJson')->andReturn([]));

    $passage = csPassage();
    // Both MC answers correct → baseline 100, no feedback.
    $result = app(ComprehensionScorer::class)->score($passage, $passage->questions, [0 => 0, 1 => 1]);

    expect($result['score'])->toBe(100)
        ->and($result['feedback'])->toBeNull();
})->group('scenario:DR-07');
