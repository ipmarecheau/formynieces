<?php

// tests/Feature/DiagnosticIntroTest.php

use App\Models\User;

test('the diagnostic intro frames the start as an expedition with a single begin action', function () {
    $student = User::factory()->create([
        'role' => 'student',
        'onboarding_completed_at' => null,
    ]);

    $response = $this->actingAs($student)->get(route('diagnostic.intro'));
    $response->assertOk();

    // Expedition framing is present and personalised
    $response->assertSee('expedition', false);
    $response->assertSee(explode(' ', $student->name)[0]);

    // Smooth introduces himself as the guide the child can always ask
    $response->assertSee('Smooth', false);
    $response->assertSee('images/voyage/companion/smooth.webp', false);

    // The child-facing diagnostic uses no test/score/timer language
    $response->assertDontSee('score', false);
    $response->assertDontSee('timer', false);
    $response->assertDontSee('minutes', false);

    // Exactly one way forward
    $response->assertSee('Set sail');
})->group('scenario:DG-01');

test('the diagnostic intro is broken into click-through steps, not one wall of text', function () {
    $student = User::factory()->create(['role' => 'student', 'onboarding_completed_at' => null]);

    $html = $this->actingAs($student)->get(route('diagnostic.intro'))->getContent();

    // Four staged panels + at least one "next" control, and only the first panel starts active.
    expect(substr_count($html, 'data-step='))->toBe(4)
        ->and($html)->toContain('data-next')
        ->and(substr_count($html, 'di-step is-active'))->toBe(1);
})->group('scenario:DG-01');
