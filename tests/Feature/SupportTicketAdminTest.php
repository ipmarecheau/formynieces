<?php

use App\Filament\Resources\SupportTickets\Pages\ListSupportTickets;
use App\Models\SupportTicket;
use App\Models\User;
use Filament\Actions\Testing\TestAction;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

/*
 * The admin side of the in-app help inbox (HC-05): the team sees every ticket and moves it
 * from open through triaged to resolved, and can play a private voice note.
 */

beforeEach(function () {
    $this->actingAs(User::factory()->create(['role' => 'admin']));
});

it('lists help tickets for the team', function () {
    $tickets = SupportTicket::factory()->count(3)->create();

    Livewire::test(ListSupportTickets::class)
        ->assertCanSeeTableRecords($tickets);
})->group('scenario:HC-05');

it('triages an open ticket', function () {
    $ticket = SupportTicket::factory()->create(['status' => SupportTicket::STATUS_OPEN]);

    Livewire::test(ListSupportTickets::class)
        ->callAction(TestAction::make('triage')->table($ticket));

    expect($ticket->fresh()->status)->toBe(SupportTicket::STATUS_TRIAGED);
})->group('scenario:HC-05');

it('resolves a ticket with resolution notes', function () {
    $ticket = SupportTicket::factory()->create(['status' => SupportTicket::STATUS_TRIAGED]);

    Livewire::test(ListSupportTickets::class)
        ->callAction(TestAction::make('resolve')->table($ticket), ['admin_notes' => 'Fixed the coming-soon bank.']);

    $fresh = $ticket->fresh();
    expect($fresh->status)->toBe(SupportTicket::STATUS_RESOLVED);
    expect($fresh->admin_notes)->toBe('Fixed the coming-soon bank.');
})->group('scenario:HC-05');

it('streams a private voice note only to an admin', function () {
    Storage::fake('local');
    Storage::disk('local')->put('support-audio/note.webm', 'audiobytes');
    $ticket = SupportTicket::factory()->withVoiceNote('support-audio/note.webm')->create();

    // Admin (already acting) can stream it.
    $this->get(route('support.audio', $ticket))->assertOk();

    // A guardian cannot.
    $this->actingAs(User::factory()->create(['role' => 'guardian']))
        ->get(route('support.audio', $ticket))->assertForbidden();
})->group('scenario:HC-05');

it('404s when a ticket has no stored voice note', function () {
    $ticket = SupportTicket::factory()->create(['audio_path' => null]);

    $this->get(route('support.audio', $ticket))->assertNotFound();
})->group('scenario:HC-05');
