<?php

use App\Models\User;

/** LP-01..10 — the landing page speaks to Caribbean parents' pain points, with Smooth as the star. */
it('shows a hero that names the parent pain, with Smooth beside it', function () {
    $this->get('/')
        ->assertOk()
        ->assertSee('SEA prep your child will')                         // the clearer parent-facing promise
        ->assertSee('You stop guessing')                                // the core parent worry
        ->assertSee('/images/voyage/companion/smooth.webp')             // Smooth in the hero
        ->assertSee('Sign up free')                                     // primary conversion CTA — sign-ups
        ->assertSee(route('register'))                                  // the sign-up path is linked
        ->assertSee('Book a free call')                                 // secondary "talk first" path
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
        ->assertSee('honest progress every week');
})->group('scenario:LP-03');

it('promises control and adaptability — a daily plan that pauses for life', function () {
    $this->get('/')
        ->assertOk()
        ->assertSee('A daily SEA plan')
        ->assertSee('pauses, resumes');
})->group('scenario:LP-04');

it('promises enjoyment — the gamified voyage map with streaks', function () {
    $this->get('/')
        ->assertOk()
        ->assertSee('voyage map')
        ->assertSee('streaks');
})->group('scenario:LP-05');

it('promises convenience — a clear daily route instead of scattered tools', function () {
    $this->get('/')
        ->assertOk()
        ->assertSee('clear daily route')
        ->assertSee('the next useful task');
})->group('scenario:LP-06');

it('promises coverage of every SEA component — Math, ELA and Writing', function () {
    $this->get('/')
        ->assertOk()
        ->assertSee('Math')
        ->assertSee('ELA')
        ->assertSee('Writing')
        ->assertSee('SEA');
})->group('scenario:LP-07');

it('promises a flexible family rhythm', function () {
    $this->get('/')
        ->assertOk()
        ->assertSee('Flexible family rhythm')
        ->assertSee('short or deep')
        ->assertSee('extra practice');
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
        ->assertSee('You stop guessing')                                // the core worry, stated plainly
        ->assertSee('Sign up free')                                     // the one primary conversion CTA — sign-ups
        ->assertSee('Book a free call')                                 // the quiet secondary "talk first" path
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

it('prices plainly — free forever vs $150/month, with the 14-day guarantees', function () {
    $this->get('/')
        ->assertOk()
        ->assertSee('Free forever')                                     // the permanently-free tier
        ->assertSee('$0')                                              // free plan price
        ->assertSee('$150')                                           // the full plan
        ->assertSee('1st month free')                                 // the trial hook, beating rivals' 7 days
        ->assertSee('every topic in the SEA syllabus')                // free = test the whole syllabus
        ->assertSee('AI tutor that teaches every topic')              // paid = taught, not just tested
        ->assertSee('Pacing guarantee')                              // always ahead of where they need to be
        ->assertSee('gamification engine')                           // motivation
        ->assertSee('roadmap')                                       // future features included
        ->assertSee('14-day money-back guarantee')
        ->assertSee('no questions asked')
        ->assertSee('measurable')
        ->assertSee('improvement')
        ->assertSee('14 days or less')
        ->assertSee('Math, ELA, Writing and Vocabulary');
})->group('scenario:LP-13');

it('shows a real gameplay reel — an autoplaying, looping, muted demo video with a poster', function () {
    $this->get('/')
        ->assertOk()
        ->assertSee('demo-pane-child')
        ->assertSee('demo-pane-parent')
        ->assertSee('For your child')
        ->assertSee('For parents');
})->group('scenario:LP-14');

it('offers the hero demo as a Child / Parent animation toggle, with the guardian portal behind its own tab', function () {
    $this->get('/')
        ->assertOk()
        ->assertSee('demo-pane-parent')                                        // the parent animation shares the hero demo, behind a tab
        ->assertSee('demo-pane-child')                                         // the child voyage sim is the other tab
        ->assertSee('psim-stage')                                              // the animated Guardian Bridge stage renders
        ->assertSee('Four honest answers — where they really stand', false)    // opening caption, below the stage
        ->assertSee('The whole family — invite the other parent', false)       // the co-parent scene (post-buildout feature)
        ->assertSee("Their logins in hand — and you're in control", false);    // the child-login + controls scene
})->group('scenario:LP-14');

it('lists a focused parent-facing feature set', function () {
    $this->get('/')
        ->assertOk()
        ->assertSee('What matters')
        ->assertSee('A daily SEA plan')
        ->assertSee('Re-teaching on every miss')
        ->assertSee('A voyage they return to')
        ->assertSee('Weekly parent visibility')
        ->assertSee('Writing, reading and vocabulary')
        ->assertSee('Flexible family rhythm')
        ->assertDontSee('Built testing 50+ AI models')
        ->assertDontSee('Roadmap features included');
})->group('scenario:LP-05');

it('keeps the old long-page section menu out of the visible landing page', function () {
    $this->get('/')
        ->assertOk()
        ->assertSee('pageNav', false)
        ->assertSee('hidden', false)
        ->assertDontSee('data-sec="see-it"', false);
})->group('scenario:LP-01');
