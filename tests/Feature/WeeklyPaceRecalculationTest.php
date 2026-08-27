<?php

use App\Models\StudentJourney;
use App\Models\SyllabusModule;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

uses(RefreshDatabase::class)->group('scenario:GD-14');

it('recalculates pace and stamps pace_recalculated_at for every active student', function () {
    $studentA = User::factory()->create(['role' => 'student']);
    $studentB = User::factory()->create(['role' => 'student']);

    foreach ([$studentA, $studentB] as $student) {
        StudentJourney::create([
            'student_id' => $student->id,
            'journey_start' => Carbon::today()->subWeeks(5)->toDateString(),
            'exam_date' => Carbon::today()->addWeeks(25)->toDateString(),
        ]);
    }

    SyllabusModule::factory()->count(6)->create(['subject' => 'Math', 'pacing_week' => 1]);

    $this->artisan('pace:weekly-recalculation')
        ->expectsOutputToContain('recalculated for 2 student(s)')
        ->assertSuccessful();

    foreach ([$studentA, $studentB] as $student) {
        expect(StudentJourney::where('student_id', $student->id)->value('pace_recalculated_at'))
            ->not->toBeNull();
    }
});

it('skips a student who has no journey', function () {
    User::factory()->create(['role' => 'student']); // no journey

    $this->artisan('pace:weekly-recalculation')
        ->expectsOutputToContain('recalculated for 0 student(s)')
        ->assertSuccessful();
});
