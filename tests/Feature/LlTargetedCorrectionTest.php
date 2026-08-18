<?php

use App\Livewire\PracticeWalk;
use App\Models\Misconception;
use App\Models\PracticeQuestion;
use App\Models\StudentProgress;
use App\Models\SyllabusModule;
use App\Models\User;
use Livewire\Livewire;

function tcStudent(): User
{
    return User::create([
        'name' => 'Maya', 'email' => 'maya-tc-'.uniqid().'@test.com',
        'password' => bcrypt('secret'), 'role' => 'student', 'onboarding_completed_at' => now(),
    ]);
}

/**
 * LL-09 — a wrong answer offers a correction targeted to her mistake: it names the
 * specific misconception behind the option she chose and shows a worked example
 * addressing it, framed as not-yet.
 */
it('names the misconception behind the chosen option and shows its worked example', function () {
    $student = tcStudent();
    $module = SyllabusModule::factory()->create();   // no lesson -> practice open
    StudentProgress::create(['student_id' => $student->id, 'module_id' => $module->id, 'status' => 'needs_work', 'current_rung' => 1]);

    Misconception::create([
        'module_id' => $module->id,
        'key' => 'swaps-tens-and-ones',
        'label' => 'It looks like the tens and ones got swapped.',
        'worked_example' => 'In 34, the 3 is thirty and the 4 is four — so 34, not 43.',
    ]);

    PracticeQuestion::factory()->create([
        'module_id' => $module->id, 'difficulty' => 1,
        'prompt' => 'What is the value of the 3 in 34?',
        'options' => ['three', 'thirty', 'four', 'forty'],
        'correct_index' => 1,
        'explanation' => 'The 3 sits in the tens place.',
        'distractor_notes' => ['misconceptions' => ['0' => 'swaps-tens-and-ones']],
    ]);

    $component = Livewire::actingAs($student)->test(PracticeWalk::class, ['module' => $module])
        ->call('choose', 0)   // first-try miss -> a gentle retry
        ->assertSet('awaitingRetry', true)
        ->call('choose', 0);  // second miss -> the targeted correction

    $feedback = $component->get('feedback');

    expect($feedback['correct'])->toBeFalse()
        ->and($feedback['misconception'])->toBe('It looks like the tens and ones got swapped.')
        ->and($feedback['worked_example'])->toBe('In 34, the 3 is thirty and the 4 is four — so 34, not 43.');
})->group('scenario:LL-09');

it('falls back to the generic explanation when the chosen option is untagged', function () {
    $student = tcStudent();
    $module = SyllabusModule::factory()->create();
    StudentProgress::create(['student_id' => $student->id, 'module_id' => $module->id, 'status' => 'needs_work', 'current_rung' => 1]);

    PracticeQuestion::factory()->create([
        'module_id' => $module->id, 'difficulty' => 1,
        'prompt' => 'What is the value of the 3 in 34?',
        'options' => ['three', 'thirty', 'four', 'forty'],
        'correct_index' => 1,
        'explanation' => 'The 3 sits in the tens place.',
        'distractor_notes' => null,   // no tags yet -> progressive enhancement fallback
    ]);

    $component = Livewire::actingAs($student)->test(PracticeWalk::class, ['module' => $module])
        ->call('choose', 2)
        ->call('choose', 2);

    $feedback = $component->get('feedback');

    expect($feedback['misconception'])->toBeNull()
        ->and($feedback['explanation'])->toBe('The 3 sits in the tens place.');
})->group('scenario:LL-09');
