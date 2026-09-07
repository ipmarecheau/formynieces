<?php

it('serves a kid-branded student sign-in at /go that posts to the shared login', function () {
    $this->get(route('student.login'))
        ->assertOk()
        ->assertSee('Sign in to your voyage')
        ->assertSee('action="'.route('login').'"', false);
});

it('cross-links the parent login to the student sign-in', function () {
    $this->get(route('login'))
        ->assertOk()
        ->assertSee(route('student.login'), false);
});
