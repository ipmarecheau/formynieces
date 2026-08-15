<?php

use App\Models\SyllabusModule;
use App\Models\User;

/** Security — student-only routes are closed to guardians and admins. */
it('forbids a guardian from a student-only route', function () {
    $module = SyllabusModule::factory()->create();
    $this->actingAs(User::factory()->create(['role' => 'guardian']));

    $this->get(route('practice.walk', $module))->assertForbidden();
});

it('forbids an admin from a student-only route', function () {
    $module = SyllabusModule::factory()->create();
    $this->actingAs(User::factory()->create(['role' => 'admin']));

    $this->get(route('practice.walk', $module))->assertForbidden();
});

it('lets a student onto a student-only route', function () {
    $module = SyllabusModule::factory()->create();
    $this->actingAs(User::factory()->create(['role' => 'student', 'onboarding_completed_at' => now()]));

    // 200 (or a domain redirect within the loop), never 403.
    $this->get(route('practice.walk', $module))->assertOk();
});

/** Security — guardian-only Parent Portal routes are closed to students. */
it('forbids a student from a guardian-only route', function () {
    $this->actingAs(User::factory()->create(['role' => 'student', 'email_verified_at' => now(), 'onboarding_completed_at' => now()]));

    $this->get(route('guardian.progress'))->assertForbidden();
});
