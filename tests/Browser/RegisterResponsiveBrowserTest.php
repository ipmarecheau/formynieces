<?php

/**
 * Captures the register page at mobile and desktop widths so the sign-up options
 * can be checked for readability (font size, alignment, wrapping).
 */
beforeEach(function () {
    foreach (['google', 'microsoft', 'facebook', 'tiktok'] as $p) {
        config(["services.{$p}.client_id" => 'test-id', "services.{$p}.client_secret" => 'test-secret']);
    }
});

it('renders readably on a mobile viewport', function () {
    $page = visit('/register');
    $page->resize(390, 844)                      // iPhone-ish
        ->assertSee('Continue with Google')
        ->assertSee('Continue with TikTok')
        ->assertNoJavascriptErrors();
    $page->screenshot(filename: 'register-mobile');
});

it('renders readably on a desktop viewport', function () {
    $page = visit('/register');
    $page->resize(1280, 900)
        ->assertSee('Continue with Google')
        ->assertNoJavascriptErrors();
    $page->screenshot(filename: 'register-desktop');
});
