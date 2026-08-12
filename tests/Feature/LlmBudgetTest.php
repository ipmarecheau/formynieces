<?php

use App\Models\StudentLlmUsage;
use App\Models\User;
use App\Services\LlmBudget;
use App\Services\LlmService;
use Illuminate\Support\Facades\Http;

function agStudent(string $suffix): User
{
    return User::create([
        'name' => 'Maya',
        'email' => "maya-ag-{$suffix}@test.com",
        'password' => bcrypt('secret'),
        'role' => 'student',
    ]);
}

/**
 * AG-01 — every LLM call attributed to a student records its real token usage and cost
 * to her month-to-date ledger; a previous month's usage does not count against this one.
 */
it('meters an LLM call to the student monthly ledger', function () {
    $student = agStudent('01a');

    config(['services.llm.key' => 'test-key']);
    Http::fake([
        '*/chat/completions' => Http::response([
            'choices' => [['message' => ['content' => 'Hello there!']]],
            'usage' => ['prompt_tokens' => 120, 'completion_tokens' => 30],
        ], 200),
    ]);

    app(LlmService::class)->complete('system', 'user', 256, $student->id);

    $row = StudentLlmUsage::where('student_id', $student->id)
        ->where('period', now()->format('Y-m'))
        ->first();

    expect($row)->not->toBeNull()
        ->and($row->input_tokens)->toBe(120)
        ->and($row->output_tokens)->toBe(30)
        ->and((float) $row->cost_usd)->toBeGreaterThan(0.0);
})->group('scenario:AG-01');

it('does not count a previous month against this month', function () {
    $student = agStudent('01b');

    StudentLlmUsage::create([
        'student_id' => $student->id,
        'period' => now()->subMonthNoOverflow()->format('Y-m'),
        'input_tokens' => 1_000_000,
        'output_tokens' => 1_000_000,
        'cost_usd' => 5.00,
    ]);

    expect(app(LlmBudget::class)->spentUsd($student->id))->toBe(0.0);
})->group('scenario:AG-01');
