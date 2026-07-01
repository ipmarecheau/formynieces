<?php

use App\Models\User;
use Filament\Facades\Filament;

it('allows an admin into the panel', function () {
    $admin = User::create([
        'name' => 'Admin',
        'email' => 'admin@test.com',
        'password' => bcrypt('password'),
        'role' => 'admin',
    ]);

    expect($admin->canAccessPanel(Filament::getDefaultPanel()))->toBeTrue();
});

it('denies a student from the panel', function () {
    $student = User::create([
        'name' => 'Aaliyah',
        'email' => 'aaliyah@students.formynieces.com',
        'password' => bcrypt('password'),
        'role' => 'student',
    ]);

    expect($student->canAccessPanel(Filament::getDefaultPanel()))->toBeFalse();
});

it('denies a guardian from the panel', function () {
    $guardian = User::create([
        'name' => 'Guardian',
        'email' => 'guardian@test.com',
        'password' => bcrypt('password'),
        'role' => 'guardian',
    ]);

    expect($guardian->canAccessPanel(Filament::getDefaultPanel()))->toBeFalse();
});
