<?php

use App\Models\StudentJourney;
use App\Models\StudentProgress;
use App\Models\SyllabusModule;
use App\Models\User;
use App\Models\WeeklyTarget;
use App\Services\Pacing\AdventureMapBuilder;
use Database\Seeders\ModulePrerequisiteSeeder;
use Database\Seeders\SyllabusModuleSeeder;

/**
 * AM — the syllabus adventure map. A world of islands (strand-families), each
 * holding a chain of levels (modules) in prerequisite order. A level unlocks
 * by mastery, never by the calendar; locked levels stay visible as
 * silhouettes. The map is interactive (tap a level to play it), and it is
 * never a pace/percentage dashboard.
 */
function makeMapStudent(): User
{
    $student = User::create([
        'name' => 'Aaliyah',
        'email' => 'map-'.uniqid().'@students.formynieces.com',
        'password' => bcrypt('secret'),
        'role' => 'student',
        'onboarding_completed_at' => now(),
    ]);

    StudentJourney::create([
        'student_id' => $student->id,
        'journey_start' => today()->subWeeks(2),
        'exam_date' => today()->addWeeks(28),
    ]);

    return $student;
}

it('groups the syllabus into island worlds, each holding a chain of levels', function () {
    $this->seed(SyllabusModuleSeeder::class);
    $this->seed(ModulePrerequisiteSeeder::class);
    $student = makeMapStudent();

    $islands = app(AdventureMapBuilder::class)->build($student);

    // Math strands all bucket into Number Isle; ELA seeds both Story Cove and
    // Word Harbour strands — both islands should be present.
    expect($islands)->toHaveKey('Number Isle')
        ->and($islands)->toHaveKey('Story Cove')
        ->and($islands)->toHaveKey('Word Harbour');

    // Every module appears exactly once, across all islands' level chains.
    $totalLevels = collect($islands)->sum(fn ($island) => count($island['levels']));
    expect($totalLevels)->toBe(SyllabusModule::count());

    // Each level carries the fields the map needs to render.
    foreach ($islands['Number Isle']['levels'] as $level) {
        expect($level)->toHaveKeys(['id', 'topic', 'subject', 'state']);
    }
})->group('scenario:AM-01');

it('renders one island section per strand-world on the map page', function () {
    $this->seed(SyllabusModuleSeeder::class);
    $this->seed(ModulePrerequisiteSeeder::class);
    $student = makeMapStudent();

    $this->actingAs($student)->get(route('student.map'))
        ->assertOk()
        ->assertSee('Number Isle')
        ->assertSee('Story Cove')
        ->assertSee('Word Harbour');
})->group('scenario:AM-01');

it('unlocks a level once its prerequisites are mastered', function () {
    $prereq = SyllabusModule::factory()->create(['subject' => 'Math', 'topic' => 'Number Concepts: Basics', 'sequence_order' => 1]);
    $dependent = SyllabusModule::factory()->create(['subject' => 'Math', 'topic' => 'Number Concepts: Advanced', 'sequence_order' => 2]);
    DB::table('module_prerequisites')->insert(['module_id' => $dependent->id, 'prerequisite_module_id' => $prereq->id, 'created_at' => now(), 'updated_at' => now()]);

    $student = makeMapStudent();
    StudentProgress::create(['student_id' => $student->id, 'module_id' => $prereq->id, 'status' => 'mastered', 'score' => 3]);

    $islands = app(AdventureMapBuilder::class)->build($student);
    $byId = collect($islands['Number Isle']['levels'])->keyBy('id');

    expect($byId[$prereq->id]['state'])->toBe('mastered')
        ->and($byId[$dependent->id]['state'])->toBe('playable');
})->group('scenario:AM-02');

it('keeps a level locked while its prerequisites are unmet', function () {
    $prereq = SyllabusModule::factory()->create(['subject' => 'Math', 'topic' => 'Number Concepts: Basics', 'sequence_order' => 1]);
    $dependent = SyllabusModule::factory()->create(['subject' => 'Math', 'topic' => 'Number Concepts: Advanced', 'sequence_order' => 2]);
    DB::table('module_prerequisites')->insert(['module_id' => $dependent->id, 'prerequisite_module_id' => $prereq->id, 'created_at' => now(), 'updated_at' => now()]);

    // Prerequisite untouched — dependent must stay locked.
    $student = makeMapStudent();

    $islands = app(AdventureMapBuilder::class)->build($student);
    $byId = collect($islands['Number Isle']['levels'])->keyBy('id');

    expect($byId[$dependent->id]['state'])->toBe('locked');

    // Locked levels are still visible on the page (as a silhouette), not hidden.
    $this->actingAs($student)->get(route('student.map'))
        ->assertOk()
        ->assertSee('data-level-id="'.$dependent->id.'"', false);
})->group('scenario:AM-03');

it('lets her tap a playable or mastered level to play it, but not a locked one', function () {
    $prereq = SyllabusModule::factory()->create(['subject' => 'Math', 'topic' => 'Number Concepts: Basics', 'sequence_order' => 1]);
    $playable = SyllabusModule::factory()->create(['subject' => 'Math', 'topic' => 'Number Concepts: Mid', 'sequence_order' => 2]);
    $locked = SyllabusModule::factory()->create(['subject' => 'Math', 'topic' => 'Number Concepts: Advanced', 'sequence_order' => 3]);
    DB::table('module_prerequisites')->insert([
        ['module_id' => $playable->id, 'prerequisite_module_id' => $prereq->id, 'created_at' => now(), 'updated_at' => now()],
        ['module_id' => $locked->id, 'prerequisite_module_id' => $playable->id, 'created_at' => now(), 'updated_at' => now()],
    ]);

    $student = makeMapStudent();
    StudentProgress::create(['student_id' => $student->id, 'module_id' => $prereq->id, 'status' => 'mastered', 'score' => 3]);

    $response = $this->actingAs($student)->get(route('student.map'));

    // Mastered and playable levels link to play; the locked level does not.
    $response->assertOk()
        ->assertSee(route('practice.walk', $prereq->id), false)
        ->assertSee(route('practice.walk', $playable->id), false)
        ->assertDontSee(route('practice.walk', $locked->id), false);
})->group('scenario:AM-04');

it('stars this week\'s suggested levels without blocking any other unlocked level', function () {
    $prereq = SyllabusModule::factory()->create(['subject' => 'Math', 'topic' => 'Number Concepts: Basics', 'sequence_order' => 1]);
    $suggested = SyllabusModule::factory()->create(['subject' => 'Math', 'topic' => 'Number Concepts: Suggested', 'sequence_order' => 2]);
    $alsoUnlocked = SyllabusModule::factory()->create(['subject' => 'Math', 'topic' => 'Number Concepts: Also Open', 'sequence_order' => 3]);

    $student = makeMapStudent();
    WeeklyTarget::create([
        'student_id' => $student->id,
        'module_id' => $suggested->id,
        'week_start_date' => now()->startOfWeek()->toDateString(),
    ]);

    $islands = app(AdventureMapBuilder::class)->build($student);
    $byId = collect($islands['Number Isle']['levels'])->keyBy('id');

    expect($byId[$suggested->id]['suggested'])->toBeTrue()
        ->and($byId[$alsoUnlocked->id]['suggested'])->toBeFalse()
        // Every entry-point module (no prerequisites) is playable regardless
        // of whether the weekly target named it.
        ->and($byId[$alsoUnlocked->id]['state'])->toBe('playable');
})->group('scenario:AM-05');

it('shows a behind-pace student the same kind map — no warnings, no pace deficits', function () {
    $this->seed(SyllabusModuleSeeder::class);
    $this->seed(ModulePrerequisiteSeeder::class);

    // Journey started 5 weeks ago with nothing touched — a guardian view would
    // read this student as behind.
    $student = User::create([
        'name' => 'Aaliyah', 'email' => 'am06-'.uniqid().'@students.formynieces.com',
        'password' => bcrypt('secret'), 'role' => 'student', 'onboarding_completed_at' => now(),
    ]);
    StudentJourney::create(['student_id' => $student->id, 'journey_start' => today()->subWeeks(5), 'exam_date' => today()->addWeeks(28)]);

    $response = $this->actingAs($student)->get(route('student.map'));

    $response->assertOk()
        ->assertDontSee('behind')
        ->assertDontSee('deficit')
        ->assertDontSee('weeks behind');

    // Every level carries only a kind state — never a warning/failure state.
    foreach (app(AdventureMapBuilder::class)->build($student) as $island) {
        foreach ($island['levels'] as $level) {
            expect($level['state'])->toBeIn(['mastered', 'playable', 'locked']);
        }
    }
})->group('scenario:AM-06');
