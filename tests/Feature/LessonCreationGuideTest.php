<?php

use App\Filament\Pages\LessonCreationGuide;
use App\Models\User;
use Livewire\Livewire;

/** LB-05 — the admin lesson-creation guide explains the Claude Code workflow, the re-teach fields, and the per-level minimum. */
it('shows admins how to generate lessons with Claude Code, the re-teach fields, and the question minimum', function () {
    $this->actingAs(User::factory()->create(['role' => 'admin']));

    Livewire::test(LessonCreationGuide::class)
        ->assertSuccessful()
        ->assertSee('Claude Code')                 // the generation workflow
        ->assertSee('lesson-authoring')            // cites the skill
        ->assertSee('practiceItems')               // the re-teach block fields
        ->assertSee('past-paper')                  // source material
        ->assertSee('15');                         // the per-level question minimum
})->group('scenario:LB-05');
