<?php

/** AB-01..04 — the about page tells the origin story honestly and funnels to a call. */
it('tells the true origin story — built for family, in the Caribbean', function () {
    $this->get(route('about'))
        ->assertOk()
        ->assertSee('two little girls')                    // began for the founder's nieces
        ->assertSee('nieces')
        ->assertSee('Trinidad')                            // Caribbean-made, named
        ->assertSee('Math, English Language Arts and Writing');
})->group('scenario:AB-01');

it('spells out the four beliefs', function () {
    $this->get(route('about'))
        ->assertOk()
        ->assertSee('Honest progress, always')             // no rosy spin for parents
        ->assertSee('No shame in re-learning')             // child never sees red ink
        ->assertSee('We work with schools')
        ->assertSee('Caribbean first');
})->group('scenario:AB-02');

it('funnels to the onboarding call at the end', function () {
    $this->get(route('about'))
        ->assertOk()
        ->assertSee('Book a free 15-minute call')
        ->assertSee(route('book.call'));
})->group('scenario:AB-03');

it('features Smooth with his real artwork and explains the turtle', function () {
    $this->get(route('about'))
        ->assertOk()
        ->assertSee('/images/voyage/companion/smooth.webp')
        ->assertSee('/images/voyage/companion/smooth-chart.webp')
        ->assertSee('Why a turtle?');
})->group('scenario:AB-04');
