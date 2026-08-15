<?php

use App\Filament\Resources\ContactMessages\Pages\ListContactMessages;
use App\Models\ContactMessage;
use App\Models\User;
use Livewire\Livewire;

/** CU-01..03 — the contact form stores messages for the team; the team sees them in the panel. */
it('stores a visitor message and confirms receipt', function () {
    $response = $this->post(route('contact.send'), [
        'name' => 'Maria Joseph',
        'email' => 'maria@example.com',
        'topic' => 'onboarding',
        'message' => 'I have two children — can one account cover both?',
    ]);

    $response->assertRedirect(route('contact'));
    $this->get(route('contact'))->assertSee('Message received');

    expect(ContactMessage::where('email', 'maria@example.com')->exists())->toBeTrue();
})->group('scenario:CU-01');

it('refuses an incomplete message politely', function () {
    $response = $this->post(route('contact.send'), [
        'name' => '',
        'email' => 'not-an-email',
        'topic' => 'nope',
        'message' => '',
    ]);

    $response->assertSessionHasErrors(['name', 'email', 'topic', 'message']);
    expect(ContactMessage::count())->toBe(0);
})->group('scenario:CU-02');

it('shows messages to admins in the panel, markable handled', function () {
    $this->actingAs(User::factory()->create(['role' => 'admin']));
    $message = ContactMessage::create([
        'name' => 'Maria Joseph',
        'email' => 'maria@example.com',
        'topic' => 'billing',
        'message' => 'Question about my refund.',
    ]);

    Livewire::test(ListContactMessages::class)
        ->assertCanSeeTableRecords(collect([$message]));
})->group('scenario:CU-03');
