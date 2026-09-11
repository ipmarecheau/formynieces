<?php

use App\Models\User;

it('shows the restored SEA coach hero with guest CTAs', function () {
    $this->get('/')
        ->assertOk()
        ->assertSee('what to practise next')
        ->assertSee('No worksheet')
        ->assertSee('/images/voyage/companion/smooth.webp')
        ->assertSee('Sign up free')
        ->assertSee('Book a free call')
        ->assertSee('Sign In')
        ->assertSee(route('register'));
})->group('scenario:LP-01');

it('offers a signed-in visitor her dashboard instead of the sales pitch', function () {
    $this->actingAs(User::factory()->create(['role' => 'student']))
        ->get('/')
        ->assertOk()
        ->assertSee('Go to your dashboard');
})->group('scenario:LP-01');

it('keeps Smooth as the guide and re-teacher', function () {
    $this->get('/')
        ->assertOk()
        ->assertSee('a turtle named Smooth')
        ->assertSee('He shows them the way')
        ->assertSee('never scolds')
        ->assertSee('He celebrates');
})->group('scenario:LP-02');

it('promises parent visibility and actions', function () {
    $this->get('/')
        ->assertOk()
        ->assertSee('Parent Portal')
        ->assertSee('re-teach')
        ->assertSee('parent actions');
})->group('scenario:LP-03');

it('promises a daily SEA plan', function () {
    $this->get('/')
        ->assertOk()
        ->assertSee('Daily 10-minute plan')
        ->assertSee('A clear next step every day');
})->group('scenario:LP-04');

it('promises enjoyment through the voyage map with streaks', function () {
    $this->get('/')
        ->assertOk()
        ->assertSee('voyage map')
        ->assertSee('streaks');
})->group('scenario:LP-05');

it('promises SEA coverage', function () {
    $this->get('/')
        ->assertOk()
        ->assertSee('Math')
        ->assertSee('ELA')
        ->assertSee('Writing')
        ->assertSee('SEA');
})->group('scenario:LP-07');

it('leads with one clear hero and no old carousel', function () {
    $this->get('/')
        ->assertOk()
        ->assertSee('what to practise next')
        ->assertSee('Sign up free')
        ->assertSee('Book a free call')
        ->assertDontSee('jumbo-dot');
})->group('scenario:LP-11');

it('speaks to parents of both boys and girls', function () {
    $this->get('/')
        ->assertOk()
        ->assertSee('SmoothSeas')
        ->assertSee('your child')
        ->assertDontSee('daughter')
        ->assertDontSee(' girls');
})->group('scenario:LP-12');

it('prices with the restored SEA pricing anchors', function () {
    $this->get('/')
        ->assertOk()
        ->assertSee('Free forever')
        ->assertSee('$0')
        ->assertSee('$79')
        ->assertSee('suggested launch price')
        ->assertSee('14-day money-back guarantee')
        ->assertSee('Math, ELA, Writing and Vocabulary');
})->group('scenario:LP-13');

it('shows the hero demo tabs from the restored version', function () {
    $this->get('/')
        ->assertOk()
        ->assertSee('demo-pane-child')
        ->assertSee('demo-pane-parent')
        ->assertSee('For your child')
        ->assertSee('For parents');
})->group('scenario:LP-14');

it('lists the restored SEA-specific feature set', function () {
    $this->get('/')
        ->assertOk()
        ->assertSee('What SmoothSeas owns')
        ->assertSee('Diagnostic first')
        ->assertSee('Daily 10-minute plan')
        ->assertSee('Verified explanations')
        ->assertSee('SEA writing feedback')
        ->assertSee('Parent action dashboard')
        ->assertSee('Readiness and school planning')
        ->assertDontSee('Built testing 50+ AI models')
        ->assertDontSee('Roadmap features included');
})->group('scenario:LP-05');

it('keeps the old long-page section menu hidden', function () {
    $this->get('/')
        ->assertOk()
        ->assertSee('pageNav', false)
        ->assertSee('hidden', false)
        ->assertDontSee('data-sec="see-it"', false);
})->group('scenario:LP-01');
