<?php

use App\Models\User;

it('lets a verified guardian create a linked student profile', function () {
    $guardian = User::factory()->create([
        'role' => 'guardian',
        'age_attested_at' => now(),
    ]);

    $response = $this->actingAs($guardian)->post('/child-setup', [
        'name' => 'Aaliyah',
        'username' => 'aaliyah',
        'password' => 'childpass123',
        'password_confirmation' => 'childpass123',
        'target_sea_year' => 2027,
        'known_weak_areas' => ['Fractions', 'Reading Comprehension'],
    ]);

    $student = User::where('role', 'student')->first();

    expect($student)->not->toBeNull()
        ->and($student->name)->toBe('Aaliyah')
        ->and($student->email)->toBe('aaliyah@students.formynieces.com')
        ->and($student->parent_id)->toBe($guardian->id)
        ->and($student->target_sea_year)->toBe(2027)
        ->and($student->known_weak_areas)->toBe(['Fractions', 'Reading Comprehension'])
        ->and($student->onboarding_completed_at)->toBeNull();

    // Credentials shown once: the controller flashes them to the session.
    $response->assertSessionHas('student_credentials');
});