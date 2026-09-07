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
