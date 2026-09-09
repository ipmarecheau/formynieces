<?php

use App\Livewire\DiagnosticWalk;
use App\Models\User;
use App\Services\Diagnostic\SessionLifecycle;
use Database\Seeders\ElaAnchorQuestionSeeder;
use Database\Seeders\MathAnchorQuestionSeeder;
use Database\Seeders\ModulePrerequisiteSeeder;
use Database\Seeders\SyllabusModuleSeeder;
use Database\Seeders\WritingAnchorQuestionSeeder;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(SyllabusModuleSeeder::class);
    $this->seed(ModulePrerequisiteSeeder::class);
    $this->seed(MathAnchorQuestionSeeder::class);
    $this->seed(ElaAnchorQuestionSeeder::class);
    $this->seed(WritingAnchorQuestionSeeder::class);

    $this->student = User::factory()->create(['role' => 'student', 'onboarding_completed_at' => now()]);
    app(SessionLifecycle::class)->startOrResume($this->student->id);
});

it('randomizes the correct answer position and still scores correctly', function () {
    $component = Livewire::actingAs($this->student)->test(DiagnosticWalk::class);

    $correctPositions = [];

    for ($i = 0; $i < 100; $i++) {
        if ($component->get('showInterstitial')) {
            $component->call('continueFromInterstitial');
        }
        $question = $component->get('question');
        if ($question === null) {
            break;
        }

        $anchor = DB::table('anchor_questions')->find($question['anchor_id']);
        $order = $component->get('optionOrder');

        // The display position the correct answer landed in this item.
        $correctPositions[] = array_search((int) $anchor->correct_index, $order, true);

        // Answer correctly by tapping that position (proves display->original mapping).
        $component->call('choose', array_search((int) $anchor->correct_index, $order, true));
    }

    // Enough items to judge, the correct answer moved around (not always on top),
    // and every "correct" tap was actually scored correct.
    expect(count($correctPositions))->toBeGreaterThan(4)
        ->and(collect($correctPositions)->unique()->count())->toBeGreaterThan(1);

    $responses = DB::table('diagnostic_responses')->where('diagnostic_session_id', $this->student->diagnosticSessions()->value('id'));
    expect((clone $responses)->count())->toBe(count($correctPositions))
        ->and((clone $responses)->where('is_correct', true)->count())->toBe(count($correctPositions));
})->group('scenario:DG-01');
