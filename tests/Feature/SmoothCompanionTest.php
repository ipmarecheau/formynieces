<?php

use App\Livewire\SmoothGuide;
use App\Models\User;
use App\Services\LlmBudget;
use Livewire\Livewire;

beforeEach(function () {
    $this->student = User::factory()->create(['role' => 'student', 'onboarding_completed_at' => now()]);
    // Skip the auto-opening how-to so we can drive the chat directly.
    $this->student->markGuideSeen('voyage');
});

it('opens the ask-anything chat from the companion FAB', function () {
    Livewire::actingAs($this->student)
        ->test(SmoothGuide::class, ['guide' => 'voyage'])
        ->assertSet('chatOpen', false)
        ->call('openChat')
        ->assertSet('chatOpen', true)
        ->assertSee('Ask Smooth');
});

it('locks and unlocks the chat on focus events', function () {
    Livewire::actingAs($this->student)
        ->test(SmoothGuide::class, ['guide' => 'voyage'])
        ->call('openChat')
        ->assertSet('chatOpen', true)
        ->dispatch('smooth:lock')
        ->assertSet('locked', true)
        ->assertSet('chatOpen', false)     // a focus moment closes the panel
        ->call('openChat')
        ->assertSet('chatOpen', false)     // and refuses to reopen while locked
        ->dispatch('smooth:unlock')
        ->assertSet('locked', false)
        ->call('openChat')
        ->assertSet('chatOpen', true);
});

it('will not send a message while locked', function () {
    Livewire::actingAs($this->student)
        ->test(SmoothGuide::class, ['guide' => 'writing', 'locked' => true])
        ->set('draft', 'help me write this')
        ->call('sendToSmooth')
        ->assertCount('messages', 0);      // nothing sent while focused
});

it('gives a kind rest message instead of calling the LLM when the budget is spent', function () {
    $this->mock(LlmBudget::class)
        ->shouldReceive('canSpend')->andReturnFalse();

    Livewire::actingAs($this->student)
        ->test(SmoothGuide::class, ['guide' => 'voyage'])
        ->call('openChat')
        ->set('draft', 'What is a fraction?')
        ->call('sendToSmooth')
        ->assertCount('messages', 2)                       // the child's line + Smooth's rest reply
        ->assertSee('rest right now');
});
