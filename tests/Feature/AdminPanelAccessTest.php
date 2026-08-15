<?php

use App\Models\User;

/** Security — the Filament admin panel is admin-only: students and guardians are forbidden. */
it('lets an admin into the panel but forbids students and guardians', function () {
    $panel = filament()->getPanel('admin');

    expect(User::factory()->create(['role' => 'admin'])->canAccessPanel($panel))->toBeTrue()
        ->and(User::factory()->create(['role' => 'student'])->canAccessPanel($panel))->toBeFalse()
        ->and(User::factory()->create(['role' => 'guardian'])->canAccessPanel($panel))->toBeFalse();
});

it('forbids a student from loading an admin panel route', function () {
    $this->actingAs(User::factory()->create(['role' => 'student', 'onboarding_completed_at' => now()]));

    $this->get('/admin/lesson-creation-guide')->assertForbidden();
});

it('forbids a guardian from loading an admin panel route', function () {
    $this->actingAs(User::factory()->create(['role' => 'guardian']));

    $this->get('/admin/lesson-creation-guide')->assertForbidden();
});

it('lets an admin load an admin panel route', function () {
    $this->actingAs(User::factory()->create(['role' => 'admin']));

    $this->get('/admin/lesson-creation-guide')->assertSuccessful();
});
