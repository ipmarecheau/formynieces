<?php

use App\Models\User;

/** LP-01..10 — the landing page speaks to Caribbean parents' pain points, with Smooth as the star. */
it('shows a hero that names the parent pain, with Smooth beside it', function () {
    $this->get('/')
        ->assertOk()
        ->assertSee('never have to guess')                              // the core worry, in the headline
        ->assertSee('/images/voyage/companion/smooth.webp')             // Smooth in the hero
        ->assertSee('Start the voyage')                                  // primary CTA
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

it('promises coverage of every ELA strand, tied to the SEA', function () {
    $this->get('/')
        ->assertOk()
        ->assertSee('Grammar')
        ->assertSee('Vocabulary')
        ->assertSee('Reading comprehension')
        ->assertSee('Writing')
        ->assertSee('SEA');
})->group('scenario:LP-07');

it('promises effectiveness — a daily plan, morning vocabulary, reading assignments', function () {
    $this->get('/')
        ->assertOk()
        ->assertSee('daily study plan')
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

it('cycles the core messages in an auto-rotating hero jumbotron', function () {
    $this->get('/')
        ->assertOk()
        ->assertSee('aria-roledescription', false)                     // the jumbotron carousel region
        ->assertSee('never have to guess')                            // slide 1: visibility
        ->assertSee('plans itself — around your child')                 // slide 2: adaptability
        ->assertSee('ask to log in')                                   // slide 3: enjoyment
        ->assertSee('in one harbour')                                   // slide 4: convenience
        ->assertSee('Twenty focused minutes a day')                     // slide 5: effectiveness
        ->assertSee('jumbo-dot');                                       // jump-to-message dots
})->group('scenario:LP-11');

it('speaks to parents of both boys and girls, as SmoothSeas', function () {
    $response = $this->get('/');
    $response->assertOk()
        ->assertSee('SmoothSeas')
        ->assertSee('your child')
        ->assertDontSee('daughter')
        ->assertDontSee(' girls');
})->group('scenario:LP-12');
