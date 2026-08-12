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

/** Seed a student already at $spent this month, and fake a cheap LLM response. */
function agStudentAtSpend(string $suffix, float $spent): User
{
    $student = agStudent($suffix);
    config(['services.llm.key' => 'test-key']);
    StudentLlmUsage::create([
        'student_id' => $student->id,
        'period' => now()->format('Y-m'),
        'input_tokens' => 0,
        'output_tokens' => 0,
        'cost_usd' => $spent,
    ]);
    Http::fake([
        '*/chat/completions' => Http::response([
            'choices' => [['message' => ['content' => 'ok']]],
            'usage' => ['prompt_tokens' => 1, 'completion_tokens' => 1],
        ], 200),
    ]);

    return $student;
}

it('stops discretionary AI at the soft cap but keeps essential AI running', function () {
    $student = agStudentAtSpend('02', 1.00);
    $svc = app(LlmService::class);

    $svc->complete('s', 'u', 64, $student->id, essential: false);
    Http::assertSentCount(0);   // discretionary: no call at the soft cap

    $svc->complete('s', 'u', 64, $student->id, essential: true);
    Http::assertSentCount(1);   // essential still runs
})->group('scenario:AG-02');

it('stops all AI at the hard ceiling', function () {
    $student = agStudentAtSpend('03', 1.50);
    $svc = app(LlmService::class);

    $svc->complete('s', 'u', 64, $student->id, essential: false);
    $svc->complete('s', 'u', 64, $student->id, essential: true);

    Http::assertSentCount(0);   // neither discretionary nor essential is called
})->group('scenario:AG-03');

it('checks the budget before the call, so no request is sent and nothing is billed', function () {
    $student = agStudentAtSpend('04', 1.50);
    $before = app(LlmBudget::class)->spentUsd($student->id);

    app(LlmService::class)->complete('s', 'u', 64, $student->id, essential: true);

    Http::assertNothingSent();
    expect(app(LlmBudget::class)->spentUsd($student->id))->toBe($before);
})->group('scenario:AG-04');
