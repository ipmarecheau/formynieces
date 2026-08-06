<?php

use App\Filament\Pages\ImportQuestions;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('renders the import page with guidance and the upload action for an admin', function () {
    $this->actingAs(User::factory()->create(['role' => 'admin']));

    Livewire::test(ImportQuestions::class)
        ->assertOk()
        ->assertSee('How it works')
        ->assertActionExists('import');
});
