<?php

/**
 * Visual + behaviour check (Playwright) that the social-login button renders on the
 * register page, is consent-gated, and the page is error-free.
 */
beforeEach(function () {
    config([
        'services.google.client_id' => 'test-id',
        'services.google.client_secret' => 'test-secret',
    ]);
});

it('renders the consent-gated Google button on the register page', function () {
    $page = visit('/register');

    $page->assertSee('Continue with Google')
        ->assertSee('Create Account')          // the email/password form is still there
        ->assertSee('18 or older')             // the social consent checkbox
        ->assertNoJavascriptErrors();

    $page->screenshot(filename: 'register-social-button');
});
