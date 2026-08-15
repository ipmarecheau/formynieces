<?php

use App\Filament\Resources\OnboardingCalls\Pages\ListOnboardingCalls;
use App\Models\OnboardingCall;
use App\Models\User;
use App\Services\Onboarding\CallSlotGenerator;
use Livewire\Livewire;

/** OC-01..05 — 15-minute onboarding calls: weekday evenings, Saturdays, no double-booking. */
it('offers the right windows — weekday evenings, Saturday daytime, never Sundays or the past', function () {
    // Wednesday 12 August 2026, 10:00 AST — days run from tomorrow (Thu 13th).
    $now = new DateTimeImmutable('2026-08-12 10:00:00', new DateTimeZone('America/Barbados'));

    $days = app(CallSlotGenerator::class)->days(14, $now);

    expect($days)->not->toBeEmpty();

    foreach ($days as $day) {
        $dow = (int) (new DateTimeImmutable($day['date']))->format('N');
        expect($dow)->not->toBe(7);                                  // no Sundays
        expect($day['date'])->toBeGreaterThan('2026-08-12');         // never today or the past

        $last = end($day['slots']);
        if ($dow === 6) {
            expect($day['slots'][0])->toBe('08:00');                 // Saturday 8:00am…
            expect($last)->toBe('16:45');                            // …to 4:45pm starts
        } else {
            expect($day['slots'][0])->toBe('17:00');                 // Weekday 5:00pm…
            expect($last)->toBe('19:45');                            // …to 7:45pm starts
        }
        expect(count($day['slots']))->toBeGreaterThan(0);
    }
})->group('scenario:OC-01');

it('books a call and confirms the day and time', function () {
    // A live slot — the controller validates against the real clock (OC-02).
    $slot = app(CallSlotGenerator::class)->openKeys()->first();

    $response = $this->post(route('book.store'), [
        'parent_name' => 'Maria Joseph',
        'email' => 'maria@example.com',
        'phone' => '868-555-0123',
        'child_standard' => 'Standard 4',
        'notes' => 'Worried about writing.',
        'slot' => $slot,
    ]);

    $response->assertRedirect(route('book.call'));
    $this->get(route('book.call'))
        ->assertSee('You are on the calendar!')
        ->assertSee('Maria Joseph')
        ->assertSee('15-minute onboarding call');

    [$date, $time] = explode('|', $slot);
    expect(OnboardingCall::where('call_date', $date)->where('call_time', $time)->exists())->toBeTrue();
})->group('scenario:OC-02');

it('refuses a taken slot and hides it from availability', function () {
    $generator = app(CallSlotGenerator::class);
    $slot = $generator->openKeys()->first();
    [$date, $time] = explode('|', $slot);

    OnboardingCall::create([
        'parent_name' => 'Early Bird',
        'email' => 'early@example.com',
        'call_date' => $date,
        'call_time' => $time,
    ]);

    // The generator no longer offers it…
    expect($generator->openKeys()->contains($slot))->toBeFalse();

    // …and a second parent submitting it is refused.
    $this->post(route('book.store'), [
        'parent_name' => 'Second Parent',
        'email' => 'second@example.com',
        'slot' => $slot,
    ])->assertSessionHasErrors('slot');

    expect(OnboardingCall::where('email', 'second@example.com')->exists())->toBeFalse();
})->group('scenario:OC-03');

it('funnels the landing hero to the booking page', function () {
    $this->get('/')
        ->assertOk()
        ->assertSee('Book a free 15-minute call')
        ->assertSee(route('book.call'));
})->group('scenario:OC-04');

it('shows bookings to admins with their status', function () {
    $this->actingAs(User::factory()->create(['role' => 'admin']));
    $call = OnboardingCall::create([
        'parent_name' => 'Maria Joseph',
        'email' => 'maria@example.com',
        'child_standard' => 'Standard 4',
        'call_date' => now()->addDays(3)->toDateString(),
        'call_time' => '17:30',
    ]);

    Livewire::test(ListOnboardingCalls::class)
        ->assertCanSeeTableRecords(collect([$call]));
})->group('scenario:OC-05');
