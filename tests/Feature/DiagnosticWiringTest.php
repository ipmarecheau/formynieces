<?php
// tests/Feature/DiagnosticWiringTest.php

use App\Models\User;
use App\Services\Diagnostic\SessionLifecycle;
use Database\Seeders\ElaAnchorQuestionSeeder;
use Database\Seeders\MathAnchorQuestionSeeder;
use Database\Seeders\ModulePrerequisiteSeeder;
use Database\Seeders\SyllabusModuleSeeder;
use Database\Seeders\WritingAnchorQuestionSeeder;
use Illuminate\Support\Facades\DB;

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
});

it('starts a session and routes the student to the walk when they set sail', function () {
    $response = $this->actingAs($this->student)->get(route('diagnostic.start'));

    $response->assertRedirect(route('diagnostic.walk'));

    $this->assertDatabaseHas('diagnostic_sessions', [
        'student_id' => $this->student->id,
        'status' => 'in_progress',
    ]);
});

it('resumes the same session instead of creating a second on a repeat start', function () {
    $this->actingAs($this->student)->get(route('diagnostic.start'));
    $this->actingAs($this->student)->get(route('diagnostic.start'));

    expect(DB::table('diagnostic_sessions')->where('student_id', $this->student->id)->count())->toBe(1);
});

it('renders the walk page for a student with an active session', function () {
    $this->actingAs($this->student)->get(route('diagnostic.start'));

    $this->actingAs($this->student)->get(route('diagnostic.walk'))->assertOk();
});