<?php

/** FQ-01..05 — the FAQ answers the questions parents actually ask, and funnels to a call. */
it('answers the programme questions', function () {
    $this->get(route('faq'))
        ->assertOk()
        ->assertSee('What exactly is SmoothSeas?')
        ->assertSee('Who is it for?')
        ->assertSee('When should we start?')
        ->assertSee('Does it replace school or extra lessons?');
})->group('scenario:FQ-01');

it('answers the child-experience questions', function () {
    $this->get(route('faq'))
        ->assertOk()
        ->assertSee('How does it know what my child needs?')
        ->assertSee('My child is behind. Will this make them feel bad?')
        ->assertSee('How much time does it take each day?')
        ->assertSee('What does a re-teach actually look like?')
        ->assertSee('as little as 20 minutes or as much as two full hours')
        ->assertSee('unlimited');
})->group('scenario:FQ-02');

it('answers the parent questions', function () {
    $this->get(route('faq'))
        ->assertOk()
        ->assertSee('How do I track progress?')
        ->assertSee('What happens when life happens')
        ->assertSee('screen time')
        ->assertSee('Pause with one tap');
})->group('scenario:FQ-03');

it('answers the money and safety questions', function () {
    $this->get(route('faq'))
        ->assertOk()
        ->assertSee('$200 per month, per family')
        ->assertSee('14 days')
        ->assertSee('no questions asked')
        ->assertSee('never sold or shared')
        ->assertSee('governed');
})->group('scenario:FQ-04');

it('funnels to the onboarding call at the end', function () {
    $this->get(route('faq'))
        ->assertOk()
        ->assertSee('Book a free 15-minute call')
        ->assertSee(route('book.call'));
})->group('scenario:FQ-05');
