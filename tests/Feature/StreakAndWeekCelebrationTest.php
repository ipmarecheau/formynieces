<?php

use App\Livewire\PracticeWalk;
use App\Models\PracticeQuestion;
use App\Models\StudentProgress;
use App\Models\StudentStreak;
use App\Models\SyllabusModule;
use App\Models\User;
use App\Models\WeeklyTarget;
use Database\Seeders\ModulePrerequisiteSeeder;
use Database\Seeders\SyllabusModuleSeeder;
use Illuminate\Support\Carbon;
use Livewire\Livewire;

function celebStudent(): User
{
    return User::create([
        'name' => 'Aaliyah', 'email' => 'celeb-'.uniqid().'@students.formynieces.com',
        'password' => bcrypt('secret'), 'role' => 'student', 'onboarding_completed_at' => now(),
    ]);
}

/**
 * CE-04 — a streak celebration plays once when she next opens her Voyage, naming
 * the streak warmly (never as a metric), and never re-fires.
 */
it('plays a streak-milestone celebration on the Voyage, once', function () {
    $this->seed(SyllabusModuleSeeder::class);
    $this->seed(ModulePrerequisiteSeeder::class);

    $student = celebStudent();
    StudentStreak::create([
        'student_id' => $student->id, 'type' => 'voyage', 'count' => 7,
        'celebrated_count' => 0, 'last_activity_date' => now()->toDateString(),
    ]);

    $this->actingAs($student)->get(route('student.voyage'))
        ->assertOk()
        ->assertSee('7-day voyage streak');

    // Opening it again does not replay the same milestone.
    $this->actingAs($student)->get(route('student.voyage'))
        ->assertOk()
        ->assertDontSee('7-day voyage streak');
})->group('scenario:CE-04');

/**
 * CE-05 — completing every module in this week's target is celebrated: when the
 * last one is mastered, a week-complete celebration plays.
 */
it('plays a week-complete celebration when the last weekly target is mastered', function () {
    $student = celebStudent();
    $module = SyllabusModule::factory()->create();
    StudentProgress::create(['student_id' => $student->id, 'module_id' => $module->id, 'status' => 'needs_work', 'current_rung' => 1]);

    // The only module in this week's target.
    WeeklyTarget::create([
        'student_id' => $student->id, 'module_id' => $module->id,
        'week_start_date' => Carbon::today()->startOfWeek()->toDateString(), 'is_completed' => false,
    ]);

    foreach ([1, 3, 5] as $rung) {
        for ($i = 0; $i < 3; $i++) {
            PracticeQuestion::factory()->create([
                'module_id' => $module->id, 'difficulty' => $rung,
                'prompt' => "q{$rung}-{$i}", 'options' => ['a', 'b', 'c', 'd'], 'correct_index' => 1,
            ]);
        }
    }

    $c = Livewire::actingAs($student)->test(PracticeWalk::class, ['module' => $module]);
    for ($step = 0; $step < 9; $step++) {
        $c->call('choose', 1);
        if ($c->get('celebration') === null) {
            $c->call('next');
        } elseif ($c->get('celebration')['type'] === 'levelup') {
            $c->call('continueAfterCelebration');
        }
    }

    $c->assertSet('celebration.type', 'weekcomplete')
        ->assertSee('all done');
})->group('scenario:CE-05');
