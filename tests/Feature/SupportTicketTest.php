<?php

use App\Models\SupportTicket;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/*
 * Behaviour coverage for formynieces-spec/features/in_app_help.feature (HC-01..05).
 * Each test is tagged with the scenario id it realises so `specs:trace` can see it.
 */

it('puts a thumb-reachable help button on every screen', function () {
    // The control itself: a fixed-position button that posts to the help endpoint.
    $html = view('partials.help-button')->render();
    expect($html)->toContain('ssh-fab');
    expect($html)->toContain('position: fixed');
    expect($html)->toContain(route('support.ticket'));

    // And it is wired into every surface a user can be on — both the shared layouts
    // AND the self-contained full-page student screens (the voyage is its own HTML
    // document, not a layout), so no screen is without it.
    $surfaces = [
        'resources/views/layouts/guardian.blade.php',               // parent
        'resources/views/layouts/app.blade.php',                    // account
        'resources/views/layouts/public.blade.php',                 // public (authed)
        'resources/views/components/layouts/diagnostic.blade.php',  // student loop (practice, etc.)
        'resources/views/voyage/overworld.blade.php',               // student home (voyage map)
        'resources/views/voyage/island.blade.php',                  // student island
        'resources/views/exam-agent.blade.php',                     // exam readiness
        'resources/views/student/diagnostic-intro.blade.php',       // first-run diagnostic
        'resources/views/student/awaiting-guardian.blade.php',      // student waiting room
    ];
    foreach ($surfaces as $surface) {
        expect(file_get_contents(base_path($surface)))
            ->toContain("@include('partials.help-button')");
    }
})->group('scenario:HC-01');

it('records a typed help ticket with who and where', function () {
    $user = User::factory()->create(['role' => 'guardian']);

    $this->actingAs($user)
        ->postJson(route('support.ticket'), [
            'kind' => 'issue',
            'message' => 'The practice bank said coming soon and I was stuck.',
            'url' => 'https://smoothseas.org/voyage',
            'context' => ['path' => '/voyage', 'viewport' => '390x844'],
        ])
        ->assertCreated()
        ->assertJson(['ok' => true]);

    $ticket = SupportTicket::firstOrFail();
    expect($ticket->user_id)->toBe($user->id);
    expect($ticket->role)->toBe('guardian');
    expect($ticket->kind)->toBe('issue');
    expect($ticket->message)->toContain('coming soon');
    expect($ticket->url)->toBe('https://smoothseas.org/voyage');
    expect($ticket->context['path'])->toBe('/voyage');
    expect($ticket->status)->toBe(SupportTicket::STATUS_OPEN);
})->group('scenario:HC-02', 'scenario:HC-05');

it('stores a voice note on the private disk', function () {
    Storage::fake('local');
    $user = User::factory()->create(['role' => 'student']);

    $this->actingAs($user)
        ->post(route('support.ticket'), [
            'kind' => 'issue',
            'audio' => UploadedFile::fake()->create('voice-note.webm', 40, 'audio/webm'),
        ])
        ->assertCreated();

    $ticket = SupportTicket::firstOrFail();
    expect($ticket->role)->toBe('student');
    expect($ticket->audio_path)->not->toBeNull();
    Storage::disk('local')->assertExists($ticket->audio_path);
})->group('scenario:HC-03');

it('rejects an empty ticket with neither message nor voice note', function () {
    $this->actingAs(User::factory()->create())
        ->postJson(route('support.ticket'), ['kind' => 'issue'])
        ->assertStatus(422);

    expect(SupportTicket::count())->toBe(0);
})->group('scenario:HC-04');

it('accepts a ticket from a guest and tags the role', function () {
    $this->postJson(route('support.ticket'), [
        'message' => 'Could not find how to sign in as a parent.',
    ])->assertCreated();

    expect(SupportTicket::firstOrFail()->role)->toBe('guest');
})->group('scenario:HC-05');
