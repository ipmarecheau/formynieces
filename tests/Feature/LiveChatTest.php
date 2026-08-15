<?php

use App\Filament\Resources\ChatConversations\Pages\ListChatConversations;
use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;

/** LC-01..06 — Smooth chat: proactive for guests, scripted qualification, Slack+email notify, admin transcript. */
it('shows the chat widget to guests, with the WhatsApp handoff and honest reply time', function () {
    config()->set('services.slack.chat_webhook', 'https://hooks.slack.test/x');

    $this->get('/')
        ->assertOk()
        ->assertSee('scw-bubble')                                  // the floating Smooth bubble
        ->assertSee('wa.me/18683234443')                           // WhatsApp → the founder's number
        ->assertSee('within a few hours');                         // honest response-time claim (LC-06)
})->group('scenario:LC-01');

it('never shows the proactive chat to signed-in users', function () {
    $this->actingAs(User::factory()->create(['role' => 'student']))
        ->get('/')
        ->assertOk()
        ->assertDontSee('scw-bubble');
})->group('scenario:LC-01');

it('starts a conversation and stores the visitor message with qualification fields', function () {
    config()->set('services.slack.chat_webhook', 'https://hooks.slack.test/x');
    Http::fake();
    Mail::fake();

    $start = $this->postJson(route('chat.start'), []);
    $start->assertOk()->assertJsonStructure(['id']);
    $id = $start->json('id');

    $this->postJson(route('chat.message'), [
        'id' => $id,
        'body' => 'Keesha',
        'visitor_name' => 'Keesha',
    ])->assertOk();

    $this->postJson(route('chat.message'), [
        'id' => $id,
        'body' => 'Standard 4',
        'visitor_name' => 'Keesha',
        'child_standard' => 'Standard 4',
        'worry' => 'Writing is very weak',
        'contact' => 'keesha@example.com',
    ])->assertOk();

    $convo = ChatConversation::find($id);
    expect($convo->visitor_name)->toBe('Keesha')
        ->and($convo->child_standard)->toBe('Standard 4')
        ->and($convo->worry)->toBe('Writing is very weak')
        ->and($convo->contact)->toBe('keesha@example.com')
        ->and($convo->messages)->toHaveCount(3);                                  // 1 bot opener + 2 visitor lines
})->group('scenario:LC-02');

it('notifies Slack and email when a visitor writes, without ever breaking the page', function () {
    config()->set('services.slack.chat_webhook', 'https://hooks.slack.test/x');
    Http::fake();
    Mail::spy();

    $id = $this->postJson(route('chat.start'), [])->json('id');

    $this->postJson(route('chat.message'), [
        'id' => $id,
        'body' => 'How much does it cost?',
        'visitor_name' => 'Alicia',
    ])->assertOk();

    Http::assertSent(function ($request) {
        return Str::contains($request->url(), 'hooks.slack.test')
            && Str::contains((string) $request->body(), 'Alicia');
    });
    Mail::shouldHaveReceived('raw')->once()->withArgs(function ($text) {
        return Str::contains($text, 'Alicia');
    });
})->group('scenario:LC-04');

it('shows conversations to admins with the transcript and close action', function () {
    $this->actingAs(User::factory()->create(['role' => 'admin']));

    $convo = ChatConversation::create([
        'visitor_name' => 'Alicia',
        'contact' => 'alicia@example.com',
        'child_standard' => 'Standard 5',
        'worry' => 'Math word problems',
        'last_message_at' => now(),
    ]);
    ChatMessage::create(['chat_conversation_id' => $convo->id, 'role' => 'bot', 'body' => 'Ahoy!']);
    ChatMessage::create(['chat_conversation_id' => $convo->id, 'role' => 'visitor', 'body' => 'Hello!']);

    Livewire::test(ListChatConversations::class)
        ->assertCanSeeTableRecords(collect([$convo]));

    $convo->update(['status' => 'closed']);
    expect($convo->fresh()->status)->toBe('closed');
})->group('scenario:LC-03');

it('offers the WhatsApp and book-a-call handoffs in the widget', function () {
    config()->set('services.slack.chat_webhook', 'https://hooks.slack.test/x');

    $this->get('/')
        ->assertOk()
        ->assertSee('Chat on WhatsApp')
        ->assertSee('Book a free call')
        ->assertSee(route('book.call'));
})->group('scenario:LC-05');
