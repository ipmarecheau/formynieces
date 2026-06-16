<?php
// tests/Feature/DiagnosticQuestionTest.php

use App\Livewire\DiagnosticWalk;
use App\Models\User;
use App\Services\Diagnostic\ItemWalk;
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
    ]);

    $this->sessionId = DB::table('diagnostic_sessions')->insertGetId([
        'student_id' => $this->student->id,
        'status' => 'in_progress',
        'current_item' => 0,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    (new SessionPlanner)->planForSession($this->sessionId);
});

it('renders the current question prompt and its four options', function () {
    $q = (new ItemWalk(new SessionPlanner))->currentQuestion($this->sessionId);
    $anchor = DB::table('anchor_questions')->find($q['anchor_id']);
    $options = json_decode($anchor->options, true);

    Livewire::actingAs($this->student)
        ->test(DiagnosticWalk::class, ['sessionId' => $this->sessionId])
        ->assertSee($anchor->prompt)
        ->assertSee($options[0])
        ->assertSee($options[1])
        ->assertSee($options[2])
        ->assertSee($options[3]);
});

it('advances to the next anchor after an answer is chosen', function () {
    $walk = new ItemWalk(new SessionPlanner);
    $first = $walk->currentQuestion($this->sessionId);

    $component = Livewire::actingAs($this->student)
        ->test(DiagnosticWalk::class, ['sessionId' => $this->sessionId])
        ->call('choose', 0);

    $next = $walk->currentQuestion($this->sessionId);

    expect($next['anchor_id'])->not->toBe($first['anchor_id']);
    expect($next['item_number'])->toBe(2);
});

it('never shows the child whether an answer was right or wrong', function () {
    $component = Livewire::actingAs($this->student)
        ->test(DiagnosticWalk::class, ['sessionId' => $this->sessionId])
        ->call('choose', 0);

    $component->assertDontSee('correct', false)
        ->assertDontSee('incorrect', false)
        ->assertDontSee('wrong', false);
});