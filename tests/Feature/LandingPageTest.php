<?php

use App\Models\User;

/** LP-01..10 — the landing page speaks to Caribbean parents' pain points, with Smooth as the star. */
it('shows a hero that names the parent pain, with Smooth beside it', function () {
    $this->get('/')
        ->assertOk()
        ->assertSee('never have to guess')                              // the core worry, in the headline
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
        ->assertSee('See it in action')
        ->assertSee('A real sail through the Voyage')
        ->assertSee('reels/child-reel.mp4')                             // Safari/iOS + smaller primary source
        ->assertSee('reels/child-reel.webm')                            // the actual demo footage, not a mock
        ->assertSee('reels/child-reel-poster.png')                      // poster + reduced-motion static fallback
        ->assertSee('autoplay')
        ->assertSee('loop')
        ->assertSee('playsinline')
        ->assertSee('Turn sound on')                                   // narration is reachable via the unmute control
        ->assertSee('For your child')                                  // hero jumbotron tab — the child reel
        ->assertSee('For you (parent)')                                // hero jumbotron tab — the parent portal reel
        ->assertSee('reels/parent-reel.mp4')                           // the guardian-portal walkthrough video
        ->assertSee('reels/parent-reel.webm');
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
