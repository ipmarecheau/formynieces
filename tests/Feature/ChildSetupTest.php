<?php

use App\Models\User;

it('lets a verified guardian create a linked student profile', function () {
    $guardian = User::factory()->create([
        'role' => 'guardian',
        'age_attested_at' => now(),
    ]);

    $response = $this->actingAs($guardian)->post('/child-setup', [
        'name' => 'Aaliyah Thomas',
        'password' => 'childpass123',
        'password_confirmation' => 'childpass123',
        'target_sea_year' => 2027,
        'known_weak_areas' => ['Fractions', 'Reading Comprehension'],
    ]);

    $student = User::where('role', 'student')->first();

    // Username auto-generated: first initial + first 4 of last name -> "athom".
    expect($student)->not->toBeNull()
        ->and($student->name)->toBe('Aaliyah Thomas')
        ->and($student->email)->toBe('athom@smoothseas.org')
        ->and($student->parent_id)->toBe($guardian->id)
        ->and($student->target_sea_year)->toBe(2027)
        ->and($student->known_weak_areas)->toBe(['Fractions', 'Reading Comprehension'])
        ->and($student->onboarding_completed_at)->toBeNull();

    // Credentials shown once: the controller flashes them to the session.
    $response->assertSessionHas('student_credentials');
})->group('scenario:GO-04');

it('auto-suffixes the username when the generated one is taken', function () {
    $guardian = User::factory()->create(['role' => 'guardian', 'age_attested_at' => now()]);

    // An existing student already holds the base username "athom".
    User::factory()->create(['role' => 'student', 'email' => 'athom@smoothseas.org']);

    $this->actingAs($guardian)->post('/child-setup', [
        'name' => 'Aiden Thompson',   // a + thom -> athom (taken) -> athom1
        'password' => 'childpass123',
        'password_confirmation' => 'childpass123',
        'target_sea_year' => 2027,
    ]);

    expect(User::where('email', 'athom1@smoothseas.org')->exists())->toBeTrue();
})->group('scenario:GO-04');
