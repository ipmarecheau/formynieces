<?php

use App\Livewire\PracticeWalk;
use App\Models\PracticeQuestion;
use App\Models\SyllabusModule;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

/** Make N distinct questions at a difficulty rung (1/3/5). */
function ceRungQuestions(int $moduleId, int $rung, int $n = 3): array
{
    $qs = [];
    for ($i = 0; $i < $n; $i++) {
        $qs[] = PracticeQuestion::factory()->create([
            'module_id' => $moduleId,
            'difficulty' => $rung,
            'prompt' => "Q{$rung}-{$i}",
            'options' => ['A', 'B', 'C', 'D'],
            'correct_index' => 1,
            'explanation' => 'x',
        ]);
    }

    return $qs;
}

it('plays a level-up celebration when a difficulty level is cleared', function () {
    $student = User::factory()->create(['role' => 'student']);
    $module = SyllabusModule::factory()->create();
    ceRungQuestions($module->id, 1);

    $c = Livewire::actingAs($student)->test(PracticeWalk::class, ['module' => $module]);

    // Three distinct first-try-correct at Level 1 clears the rung.
    for ($i = 0; $i < 3; $i++) {
        $c->call('choose', 1);
        if ($i < 2) {
            $c->call('next');
        }
    }

    $c->assertSet('celebration.type', 'levelup')
        ->assertSee('Level up!')
        ->assertSeeText('on to Level 2');

    // Continue dismisses the celebration and returns to practice.
    $c->call('continueAfterCelebration')
        ->assertSet('celebration', null);
})->group('scenario:CE-02');

it('plays a headline mastery celebration that leads back to the Voyage', function () {
    $student = User::factory()->create(['role' => 'student']);
    $module = SyllabusModule::factory()->create();
    foreach ([1, 3, 5] as $rung) {
        ceRungQuestions($module->id, $rung);
    }

    $c = Livewire::actingAs($student)->test(PracticeWalk::class, ['module' => $module]);

    // Climb all three rungs; three first-try-correct per rung.
    for ($step = 0; $step < 9; $step++) {
        $c->call('choose', 1);
        // Advance to the next question except at a celebration (rung clear / mastery).
        if ($c->get('celebration') === null) {
            $c->call('next');
        } elseif ($c->get('celebration')['type'] === 'levelup') {
            $c->call('continueAfterCelebration');
        }
    }

    $c->assertSet('celebration.type', 'mastery')
        ->assertSee('You mastered it!')
        ->assertSee(route('student.voyage'));
})->group('scenario:CE-03');

it('respects reduced motion in the celebration overlay', function () {
    // The celebration markup carries the reduced-motion fallback (CE-06).
    $html = Blade::render(
        '<x-celebration title="Level up!" sub="Nice" />'
    );

    expect($html)
        ->toContain('prefers-reduced-motion')
        ->toContain('Level up!');
})->group('scenario:CE-06');
