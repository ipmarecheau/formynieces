<?php

use App\Models\User;

/** LP-01..10 — the landing page speaks to Caribbean parents' pain points, with Smooth as the star. */
it('shows a hero that names the parent pain, with Smooth beside it', function () {
    $this->get('/')
        ->assertOk()
        ->assertSee('never have to guess')                              // the core worry, in the headline
        ->assertSee('/images/voyage/companion/smooth.webp')             // Smooth in the hero
        ->assertSee('Book a free 15-minute call')                       // primary conversion CTA
        ->assertSee('Create an account')                                // direct signup path
        ->assertSee('Sign In');                                         // guest can reach login
})->group('scenario:LP-01');

it('offers a signed-in visitor her dashboard instead of the sales pitch', function () {
    $this->actingAs(User::factory()->create(['role' => 'student']))
        ->get('/')
        ->assertOk()
        ->assertSee('Go to your dashboard');
})->group('scenario:LP-01');

it('introduces Smooth properly — guide, patient re-teacher, celebrator', function () {
    $this->get('/')
        ->assertOk()
        ->assertSee('a turtle named Smooth')
        ->assertSee('He shows them the way')
        ->assertSee('never scolds')
        ->assertSee('He celebrates');
})->group('scenario:LP-02');

it('promises visibility — a weekly Parent Portal picture and a visible re-teach', function () {
    $this->get('/')
        ->assertOk()
        ->assertSee('Parent Portal')
        ->assertSee('re-teach')
        ->assertSee('Every week');
})->group('scenario:LP-03');

it('promises control and adaptability — a self-planning curriculum that pauses for life', function () {
    $this->get('/')
        ->assertOk()
        ->assertSee('plans itself')
        ->assertSee('pause and resume');
})->group('scenario:LP-04');

it('promises enjoyment — the gamified voyage map with streaks', function () {
    $this->get('/')
        ->assertOk()
        ->assertSee('voyage map')
        ->assertSee('streaks');
})->group('scenario:LP-05');

it('promises convenience — lessons, tutorials and practice in one place', function () {
    $this->get('/')
        ->assertOk()
        ->assertSee('Lessons, tutorials and practice');
})->group('scenario:LP-06');

it('promises coverage of every SEA component — Math, ELA and Writing', function () {
    $this->get('/')
        ->assertOk()
        ->assertSee('Mathematics')
        ->assertSee('English Language Arts')
        ->assertSee('Writing')
        ->assertSee('SEA');
})->group('scenario:LP-07');

it('promises a flexible daily rhythm — 20 minutes to two hours, unlimited practice', function () {
    $this->get('/')
        ->assertOk()
        ->assertSee('daily study plan')
        ->assertSee('as little as 20 minutes')
        ->assertSee('two full hours')
        ->assertSee('Unlimited practice')
        ->assertSee('morning vocabulary ritual')
        ->assertSee('reading assignments');
})->group('scenario:LP-08');

it('promises reinforcement — the parent sets the treasure', function () {
    $this->get('/')
        ->assertOk()
        ->assertSee('You set the treasure')
        ->assertSee('rewards');
})->group('scenario:LP-09');

it('promises consolidation — the school journal, honestly marked as coming', function () {
    $this->get('/')
        ->assertOk()
        ->assertSee('school papers')
        ->assertSee('Coming in the MVP');
})->group('scenario:LP-10');

it('leads with a single clear hero — the core worry and one primary CTA, no auto-rotating carousel', function () {
    $this->get('/')
        ->assertOk()
        ->assertSee('never have to guess')                              // the core worry, stated once and plainly
        ->assertSee('Book a free 15-minute call')                       // the one primary conversion CTA
        ->assertSee('Create an account')                                // the quiet secondary path
        ->assertDontSee('jumbo-dot');                                   // the old auto-rotating jumbotron is gone
})->group('scenario:LP-11');

it('speaks to parents of both boys and girls, as SmoothSeas', function () {
    $response = $this->get('/');
    $response->assertOk()
        ->assertSee('SmoothSeas')
        ->assertSee('your child')
        ->assertDontSee('daughter')
        ->assertDontSee(' girls');
})->group('scenario:LP-12');

it('prices plainly — $200/month with the 14-day money-back and improvement guarantees', function () {
    $this->get('/')
        ->assertOk()
        ->assertSee('$200')
        ->assertSee('14-day money-back guarantee')
        ->assertSee('no questions asked')
        ->assertSee('measurable')
        ->assertSee('improvement')
        ->assertSee('14 days or less')
        ->assertSee('Math, ELA, Writing and Vocabulary');
})->group('scenario:LP-13');
