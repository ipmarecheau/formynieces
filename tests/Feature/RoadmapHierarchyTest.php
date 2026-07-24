<?php

// tests/Feature/RoadmapHierarchyTest.php

use App\Models\StudentProgress;
use App\Models\SyllabusModule;
use App\Models\User;

beforeEach(function () {
    $this->student = User::create([
        'name' => 'Aaliyah',
        'email' => 'aaliyah-'.uniqid().'@students.formynieces.com',
        'password' => bcrypt('secret'),
        'role' => 'student',
        'onboarding_completed_at' => now(),
    ]);

    // Two Fractions modules, one Geometry — to prove grouping by prefix.
    $fracA = SyllabusModule::create(['subject' => 'Math', 'topic' => 'Fractions: Addition', 'sea_section' => 'Section I', 'sequence_order' => 1]);
    $fracB = SyllabusModule::create(['subject' => 'Math', 'topic' => 'Fractions: Multiplication', 'sea_section' => 'Section I', 'sequence_order' => 2]);
    $geo = SyllabusModule::create(['subject' => 'Math', 'topic' => 'Geometry: Angles', 'sea_section' => 'Section I', 'sequence_order' => 3]);
    $spell = SyllabusModule::create(['subject' => 'ELA', 'topic' => 'Spelling: Plurals', 'sea_section' => 'Section I', 'sequence_order' => 4]);

    StudentProgress::create(['student_id' => $this->student->id, 'module_id' => $fracA->id, 'status' => 'mastered', 'score' => 3]);
    StudentProgress::create(['student_id' => $this->student->id, 'module_id' => $fracB->id, 'status' => 'mastered', 'score' => 2]);
    StudentProgress::create(['student_id' => $this->student->id, 'module_id' => $geo->id, 'status' => 'needs_work', 'score' => null]);
    StudentProgress::create(['student_id' => $this->student->id, 'module_id' => $spell->id, 'status' => 'inferred_mastered', 'score' => null]);

});

// This is regression coverage for the "explore by subject" section kept below
// the adventure map — a supplementary browsing view, not itself an AM
// scenario (the adventure map redesign replaced AM-01/AM-02's old meaning).
it('groups modules under their topic prefix in the subject explorer', function () {
    $response = $this->actingAs($this->student)->get(route('student.map'));

    $response->assertOk();
    // Group headers (prefixes) appear
    $response->assertSee('Fractions');
    $response->assertSee('Geometry');
    // Leaf names (after the colon) appear
    $response->assertSee('Addition');
    $response->assertSee('Multiplication');
    $response->assertSee('Angles');
});

it('does not render the difficulty rung on the map', function () {
    $response = $this->actingAs($this->student)->get(route('student.map'));

    $response->assertOk();
    // score=3 and score=2 rungs must not surface as text. With completion at
    // 50%, "3%" / "2%" can only come from a rendered rung, not the hero bar.
    $response->assertDontSee('3%');
    $response->assertDontSee('2%');
});
