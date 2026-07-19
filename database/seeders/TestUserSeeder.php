<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TestUserSeeder extends Seeder
{
    public function run(): void
    {
        $guardian = User::updateOrCreate(
            ['email' => 'guardian@test.com'],
            [
                'name'              => 'Test Guardian',
                'password'          => Hash::make('password'),
                'role'              => 'parent',
                'email_verified_at' => now(),
            ]
        );

        User::updateOrCreate(
            ['email' => 'student@test.com'],
            [
                'name'              => 'Test Student',
                'password'          => Hash::make('password'),
                'role'              => 'student',
                'parent_id'         => $guardian->id,
                'email_verified_at' => now(),
            ]
        );

        User::updateOrCreate(
            ['email' => 'admin@test.com'],
            [
                'name'              => 'Test Admin',
                'password'          => Hash::make('password'),
                'role'              => 'admin',
                'email_verified_at' => now(),
            ]
        );
    }
}
