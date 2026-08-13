<?php

use App\Models\User;
use Filament\Facades\Filament;

it('routes an admin to the Filament panel after login, not the app dashboard', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $response = $this->post('/login', [
        'email' => $admin->email,
        'password' => 'password',
    ]);

    $response->assertRedirect(Filament::getPanel('admin')->getUrl());
});
