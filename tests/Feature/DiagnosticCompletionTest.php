<?php
// tests/Feature/DiagnosticCompletionTest.php

use App\Livewire\DiagnosticWalk;
use App\Models\User;
use App\Services\Diagnostic\ItemWalk;
use App\Services\Diagnostic\SessionLifecycle;
use App\Services\Diagnostic\SessionPlanner;
use Database\Seeders\ElaAnchorQuestionSeeder;
use Database\Seeders\MathAnchorQuestionSeeder;
use Database\Seeders\ModulePrerequisiteSeeder;
use Database\Seeders\SyllabusModuleSeeder;
use Database\Seeders\WritingAnchorQuestionSeeder;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(SyllabusModuleSeeder::class);
    $this->seed(ModulePrerequisiteSeeder::class);
    $this->seed(MathAnchorQuestionSeeder::class);
    $this->seed(ElaAnchorQuestionSeeder::class);
    $this->seed(WritingAnchorQuestionSeeder::class);

    $this->student = User::create([
        'name' => 'Aaliyah',
        'email' => 'aaliyah-' . uniqid() . '@students.formynieces.com',
        'password' => bcrypt('secret'),
        'role' => 'student',
        'onboarding_completed_at' => now(),
    ]);

    $this->sessionId = app(SessionLifecycle::class)->startOrResume($this->student->id);
});

/** Walk the whole plan via the engine, answering everything correctly. */
function walkSessionToEnd(int $sessionId): void
{
    $walk = new ItemWalk(new SessionPlanner);
    for ($i = 0; $i < 60; $i++) {
        $q = $walk->currentQuestion($sessionId);
        if ($q === null) {
            break;
        }
        $anchor = DB::table('anchor_questions')->find($q['anchor_id']);
        $walk->submitAnswer($sessionId, $q['anchor_id'], $anchor->correct_index);
    }
}

it('marks the session completed when the walk ends', function () {
    walkSessionToEnd($this->sessionId);

    // Mounting the component on a walked session should trigger completion.
    Livewire::actingAs($this->student)->test(DiagnosticWalk::class);

    $session = DB::table('diagnostic_sessions')->find($this->sessionId);
    expect($session->status)->toBe('completed');
    expect($session->completed_at)->not->toBeNull();
})->group('scenario:RR-01');

it('writes a mastery map into student_progress on completion', function () {
    walkSessionToEnd($this->sessionId);

    Livewire::actingAs($this->student)->test(DiagnosticWalk::class);

    expect(DB::table('student_progress')->where('student_id', $this->student->id)->count())
        ->toBeGreaterThan(0);
})->group('scenario:RR-01');

it('lets the student start a fresh session after completing one', function () {
    walkSessionToEnd($this->sessionId);
    Livewire::actingAs($this->student)->test(DiagnosticWalk::class);

    // The completed session must not be resumed; a new one is created.
    $newId = app(SessionLifecycle::class)->startOrResume($this->student->id);

    expect($newId)->not->toBe($this->sessionId);
})->group('scenario:DG-15');

it('shows a way forward on the completion screen', function () {
    walkSessionToEnd($this->sessionId);

    Livewire::actingAs($this->student)
        ->test(DiagnosticWalk::class)
        ->assertSee('See your map');
})->group('scenario:RR-08');