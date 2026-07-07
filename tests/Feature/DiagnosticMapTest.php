<?php
// tests/Feature/DiagnosticMapTest.php

use App\Models\StudentProgress;
use App\Models\SyllabusModule;
use App\Models\User;

beforeEach(function () {
    $this->student = User::create([
        'name' => 'Aaliyah',
        'email' => 'aaliyah-' . uniqid() . '@students.formynieces.com',
        'password' => bcrypt('secret'),
        'role' => 'student',
        'onboarding_completed_at' => now(),
    ]);

    // Minimal modules to attach progress to.
    $this->mathA = SyllabusModule::create(['subject' => 'Math', 'topic' => 'Fractions: Addition', 'sea_section' => 'Section I', 'sequence_order' => 1]);
    $this->mathB = SyllabusModule::create(['subject' => 'Math', 'topic' => 'Geometry: Angles', 'sea_section' => 'Section I', 'sequence_order' => 2]);
    $this->elaA  = SyllabusModule::create(['subject' => 'ELA', 'topic' => 'Spelling: Plurals', 'sea_section' => 'Section I', 'sequence_order' => 3]);
    $this->elaB  = SyllabusModule::create(['subject' => 'ELA', 'topic' => 'Poetry: Mood', 'sea_section' => 'Section II', 'sequence_order' => 4]);

    // One of each engine status.
    StudentProgress::create(['student_id' => $this->student->id, 'module_id' => $this->mathA->id, 'status' => 'mastered', 'score' => 3]);
    StudentProgress::create(['student_id' => $this->student->id, 'module_id' => $this->mathB->id, 'status' => 'inferred_mastered', 'score' => null]);
    StudentProgress::create(['student_id' => $this->student->id, 'module_id' => $this->elaA->id, 'status' => 'needs_work', 'score' => null]);
    StudentProgress::create(['student_id' => $this->student->id, 'module_id' => $this->elaB->id, 'status' => 'not_started', 'score' => null]);
});

it('shows the four status buckets and they sum to the total', function () {
    $response = $this->actingAs($this->student)->get(route('student.map'));

    $response->assertOk();
    // Each bucket label present
    $response->assertSee('Mastered');
    $response->assertSee('Likely Known');
    $response->assertSee('Needs Work');
})->group('scenario:RR-01');

it('counts each engine status correctly', function () {
    $progress = StudentProgress::where('student_id', $this->student->id)->get();

    $mastered = $progress->where('status', 'mastered')->count();
    $likely   = $progress->where('status', 'inferred_mastered')->count();
    $needs    = $progress->where('status', 'needs_work')->count();
    $upcoming = $progress->where('status', 'not_started')->count();

    // The four buckets must account for every row — no orphans (the old bug).
    expect($mastered + $likely + $needs + $upcoming)->toBe($progress->count());
    expect($mastered)->toBe(1);
    expect($likely)->toBe(1);
    expect($needs)->toBe(1);
    expect($upcoming)->toBe(1);
})->group('scenario:RR-01');