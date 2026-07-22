<?php

use App\Filament\Resources\CapReviews\Pages\ListCapReviews;
use App\Models\StudentJourney;
use App\Models\SyllabusModule;
use App\Models\User;
use App\Services\Pacing\WeeklyRollover;
use Livewire\Livewire;

/**
 * AC-04 — a student whose feasible pace (remaining / weeks-to-exam) needs more
 * modules per week than her cap is flagged for admin cap review, with her
 * required pace recorded. Independent of the WT-03 "weeks behind" warning.
 */
function ac04Student(int $capOverride, int $weeksToExam, int $moduleCount): User
{
    $student = User::create([
        'name' => 'AC04 Student',
        'email' => 'ac04-'.uniqid().'@example.com',
        'password' => bcrypt('password'),
        'role' => 'student',
        'weekly_module_cap_override' => $capOverride,
    ]);

    StudentJourney::create([
        'student_id' => $student->id,
        'journey_start' => now()->toDateString(),
        'exam_date' => now()->addWeeks($weeksToExam)->toDateString(),
    ]);

    collect(range(1, $moduleCount))->each(fn (int $i) => SyllabusModule::create([
        'subject' => 'Math',
        'topic' => "Number: Topic {$i}",
        'sea_section' => 'Number',
        'sequence_order' => $i,
        'pacing_week' => 1,
    ]));

    return $student;
}

it('flags a student for admin cap review when feasible pace exceeds her cap', function () {
    // cap 3, 40 modules over 5 weeks → needs ceil(40/5) = 8 modules/week > 3.
    $student = ac04Student(capOverride: 3, weeksToExam: 5, moduleCount: 40);

    app(WeeklyRollover::class)->runFor($student);

    $journey = StudentJourney::where('student_id', $student->id)->first();

    expect($journey->cap_review_required)->toBeTrue();
    expect($journey->required_pace)->toBe(8);
})->group('scenario:AC-04');

it('does not flag a student whose cap comfortably covers her remaining pace', function () {
    // cap 5, 6 modules over 30 weeks → needs ceil(6/30) = 1 module/week <= 5.
    $student = ac04Student(capOverride: 5, weeksToExam: 30, moduleCount: 6);

    app(WeeklyRollover::class)->runFor($student);

    $journey = StudentJourney::where('student_id', $student->id)->first();

    expect($journey->cap_review_required)->toBeFalse();
})->group('scenario:AC-04');

it('shows flagged students with their required pace in the admin cap-review list', function () {
    $admin = User::create([
        'name' => 'Admin',
        'email' => 'admin-ac04@test.com',
        'password' => bcrypt('password'),
        'role' => 'admin',
    ]);

    $flaggedStudent = User::create([
        'name' => 'Flagged Fiona',
        'email' => 'fiona-ac04@example.com',
        'password' => bcrypt('password'),
        'role' => 'student',
    ]);
    $flaggedJourney = StudentJourney::create([
        'student_id' => $flaggedStudent->id,
        'journey_start' => now()->toDateString(),
        'exam_date' => now()->addWeeks(5)->toDateString(),
        'cap_review_required' => true,
        'required_pace' => 8,
    ]);

    $okStudent = User::create([
        'name' => 'OnPace Olivia',
        'email' => 'olivia-ac04@example.com',
        'password' => bcrypt('password'),
        'role' => 'student',
    ]);
    $okJourney = StudentJourney::create([
        'student_id' => $okStudent->id,
        'journey_start' => now()->toDateString(),
        'exam_date' => now()->addWeeks(30)->toDateString(),
        'cap_review_required' => false,
    ]);

    $this->actingAs($admin);

    Livewire::test(ListCapReviews::class)
        ->assertCanSeeTableRecords([$flaggedJourney])
        ->assertCanNotSeeTableRecords([$okJourney])
        ->assertSee('Flagged Fiona')
        ->assertSee('Required pace');
})->group('scenario:AC-04');
