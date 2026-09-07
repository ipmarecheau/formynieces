<?php

it('renders the signup form as a step-by-step wizard (progressively enhanced)', function () {
    $this->get(route('register'))
        ->assertOk()
        ->assertSee('rw-progress', false)          // step progress bar
        ->assertSee('data-step="1"', false)
        ->assertSee('data-step="4"', false)        // four steps
        ->assertSee('name="name"', false)          // all fields still present for the single POST
        ->assertSee('name="email"', false)
        ->assertSee('name="password"', false)
        ->assertSee('name="terms"', false);
});
