<?php

it('renders sign-up as one minimal screen: email + password + consent, no name/phone/stepper', function () {
    $this->get(route('register'))
        ->assertOk()
        ->assertSee('name="email"', false)
        ->assertSee('name="password"', false)
        ->assertSee('name="terms"', false)
        ->assertSee('name="age_attestation"', false)
        ->assertDontSee('name="name"', false)      // name deferred to the onboarding wizard
        ->assertDontSee('name="phone"', false)     // phone deferred to the onboarding wizard
        ->assertDontSee('rw-progress', false);     // the multi-step signup stepper is gone
});
