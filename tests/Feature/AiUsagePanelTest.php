<?php

use App\Filament\Resources\AiUsage\Pages\ListAiUsage;
use App\Models\StudentLlmUsage;
use App\Models\User;
use Livewire\Livewire;

/**
 * AG-09 — the admin AI-usage panel lists each student's month-to-date tokens and spend
 * (against the USD 1.00 / 1.50 caps), scoped to the current month.
 */
function aiPanelAdmin(): User
{
    return User::create([
        'name' => 'Admin',
        'email' => 'admin-ag09@test.com',
        'password' => bcrypt('secret'),
        'role' => 'admin',
    ]);
}

function aiPanelStudent(string $name, string $suffix): User
{
    return User::create([
        'name' => $name,
        'email' => "student-{$suffix}@test.com",
        'password' => bcrypt('secret'),
        'role' => 'student',
    ]);
}

it('shows each student month-to-date tokens and spend to an admin', function () {
    $student = aiPanelStudent('Maya', 'ag09a');
    $usage = StudentLlmUsage::create([
        'student_id' => $student->id,
        'period' => now()->format('Y-m'),
        'input_tokens' => 1200,
        'output_tokens' => 800,
        'cost_usd' => 1.10,
    ]);

    Livewire::actingAs(aiPanelAdmin())->test(ListAiUsage::class)
        ->assertCanSeeTableRecords([$usage])
        ->assertSee('Maya')
        ->assertSee('1.10');
})->group('scenario:AG-09');

it('scopes the panel to the current month', function () {
    $student = aiPanelStudent('Maya', 'ag09b');

    $thisMonth = StudentLlmUsage::create([
        'student_id' => $student->id, 'period' => now()->format('Y-m'),
        'input_tokens' => 10, 'output_tokens' => 10, 'cost_usd' => 0.20,
    ]);
    $lastMonth = StudentLlmUsage::create([
        'student_id' => $student->id, 'period' => now()->subMonthNoOverflow()->format('Y-m'),
        'input_tokens' => 99, 'output_tokens' => 99, 'cost_usd' => 0.99,
    ]);

    Livewire::actingAs(aiPanelAdmin())->test(ListAiUsage::class)
        ->assertCanSeeTableRecords([$thisMonth])
        ->assertCanNotSeeTableRecords([$lastMonth]);
})->group('scenario:AG-09');
