<?php
// tests/Feature/DiagnosticRedesignTest.php

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

it('shows the island banner for the current strand', function () {
    // First planned item is a Math/Number slot — should map to Number Isle.
    Livewire::actingAs($this->student)
        ->test(DiagnosticWalk::class)
        ->assertSee('Number Isle');
});

it('shows the true plan total for the voyage trail, not a hardcoded number', function () {
    $total = count(json_decode(
        DB::table('diagnostic_sessions')->find($this->sessionId)->item_plan,
        true
    ));

    Livewire::actingAs($this->student)
        ->test(DiagnosticWalk::class)
        ->assertSet('planTotal', $total);
});

it('still never reveals correctness on the redesigned screen', function () {
    Livewire::actingAs($this->student)
        ->test(DiagnosticWalk::class)
        ->call('choose', 0)
        ->assertDontSee('correct', false)
        ->assertDontSee('incorrect', false)
        ->assertDontSee('wrong', false);
});