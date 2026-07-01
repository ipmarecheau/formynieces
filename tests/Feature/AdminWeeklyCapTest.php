<?php

use App\Filament\Resources\Users\Pages\EditUser;
use App\Models\User;
use Livewire\Livewire;

it('lets an admin set a student\'s weekly module cap override', function () {
    $admin = User::create([
    'name' => 'Admin',
    'email' => 'admin@test.com',
    'password' => bcrypt('password'),
    'role' => 'admin',   // was 'guardian'
    ]);

    $student = User::create([
        'name' => 'Aaliyah',
        'email' => 'aaliyah@students.formynieces.com',
        'password' => bcrypt('password'),
        'role' => 'student',
    ]);

    $this->actingAs($admin);

    Livewire::test(EditUser::class, ['record' => $student->getRouteKey()])
        ->fillForm(['weekly_module_cap_override' => 9])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($student->refresh()->weekly_module_cap_override)->toBe(9);
});
