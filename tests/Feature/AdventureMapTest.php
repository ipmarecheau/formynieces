<?php

use App\Models\StudentJourney;
use App\Models\User;
use App\Services\Pacing\AdventureMapBuilder;
use Database\Seeders\SyllabusModuleSeeder;

/**
 * AM — the week-based adventure map. One stop per content pacing week (1–21),
 * each in a state of completed / current / upcoming / locked. The map is the
 * motivational layer: always kind, always moving forward — never pace deficits.
 */
beforeEach(function () {
    $this->seed(SyllabusModuleSeeder::class);
});

function makeMapStudent(?Carbon\Carbon $journeyStart = null): User
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
        'journey_start' => $journeyStart ?? today()->subWeeks(2), // → current week 3
        'exam_date' => today()->addWeeks(28),
    ]);

    return $student;
}

it('builds one stop per pacing week, each with a valid state', function () {
    $student = makeMapStudent(today()->subWeeks(2)); // current pacing week = 3

    $stops = app(AdventureMapBuilder::class)->build($student);

    // One stop per content pacing week (1–21).
    expect($stops)->toHaveCount(21);

    $byWeek = collect($stops)->keyBy('week');
    expect($byWeek[1]['state'])->toBe('completed')  // past
        ->and($byWeek[3]['state'])->toBe('current')  // == current week
        ->and($byWeek[4]['state'])->toBe('upcoming')  // == current + 1
        ->and($byWeek[6]['state'])->toBe('locked');   // > current + 1

    // Every stop carries one of the four allowed states.
    expect(collect($stops)->pluck('state')->every(
        fn ($s) => in_array($s, ['completed', 'current', 'upcoming', 'locked'], true)
    ))->toBeTrue();
})->group('scenario:AM-01');

it('shows a behind-pace student a kind map — no warnings, no pace deficits', function () {
    // Five weeks in with nothing mastered: a guardian would read her as behind.
    $student = makeMapStudent(today()->subWeeks(5)); // current pacing week 6

    $response = $this->actingAs($student)->get(route('student.map'));

    $response->assertOk()
        ->assertDontSee('behind')
        ->assertDontSee('deficit')
        ->assertDontSee('weeks behind');

    // No stop carries a warning/failure state — only the four kind states.
    foreach (app(AdventureMapBuilder::class)->build($student) as $stop) {
        expect($stop['state'])->toBeIn(['completed', 'current', 'upcoming', 'locked']);
    }
})->group('scenario:AM-02');

it('renders the adventure map trail with a stop for every week', function () {
    $student = makeMapStudent(today()->subWeeks(2));

    $response = $this->actingAs($student)->get(route('student.map'));

    $response->assertOk();
    // A stop marker per week (1 and 21 both present → full trail rendered).
    $response->assertSee('data-stop-week="1"', false)
        ->assertSee('data-stop-week="21"', false);
})->group('scenario:AM-01');
