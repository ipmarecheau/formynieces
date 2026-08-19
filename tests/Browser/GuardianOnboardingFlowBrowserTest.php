<?php

use App\Models\User;

/**
 * Browser (Playwright) verification for the screen-backed Guardian Onboarding
 * scenarios: the registration form's framing (GO-09), the setup-journey stepper
 * (GO-10), and the email-verification notice (GO-11).
 */
it('GO-09: registration asks for the guardian\'s own name with an adult example', function () {
    $page = visit('/register');

    // Labelled as her own name; the adult-name example lives in the field's
    // placeholder (asserted here as an attribute, since placeholders are not
    // rendered as visible page text).
    $page->assertNoJavascriptErrors()
        ->assertSee('Your Name (Parent / Guardian)')
        ->assertAttribute('#name', 'placeholder', 'e.g. Maria Thomas');
});

it('GO-10: the setup journey shows which step she is on and how many remain', function () {
    $page = visit('/register');

    $page->assertNoJavascriptErrors()
        ->assertSee('Step 1 of'); // the stepper names her position in the journey
});

it('GO-11: the verification notice tells her how to resend and reach a human', function () {
    $guardian = User::factory()->create([
        'role' => 'guardian',
        'email_verified_at' => null,
    ]);
    $this->actingAs($guardian);

    $page = visit('/verify-email');

    $page->assertNoJavascriptErrors()
        ->assertSee('Resend Verification Email') // she can resend without leaving the flow
        ->assertSee('Contact us');               // and reach a real person for help
});
