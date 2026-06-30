<?php

namespace Database\Seeders;

use App\Models\StudentProgress;
use App\Models\SyllabusModule;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TestAccountSeeder extends Seeder
{
    public function run(): void
    {
        // Guardian — real email, verified, 18+ attested
        $guardian = User::updateOrCreate(
            ['email' => 'mummy1@test.com'],
            [
                'name' => 'Test Guardian',
                'password' => Hash::make('password'),
                'role' => 'guardian',
                'email_verified_at' => now(),
                'age_attested_at' => now(),
            ]
        );

        // Student — synthetic email, never verifies, onboarding complete
        $student = User::updateOrCreate(
            ['email' => 'aaliyah@students.formynieces.com'],
            [
                'name' => 'Aaliyah',
                'password' => Hash::make('password'),
                'role' => 'student',
                'parent_id' => $guardian->id,
                'onboarding_completed_at' => now(),
                'target_sea_year' => 2027,
            ]
        );

        // A needs_work module so /my-map populates and practice is reachable.
        // Module 1 is one of the 4 with seeded practice questions (1, 3, 52, 73).
        $module = SyllabusModule::find(1);
        if ($module) {
            StudentProgress::updateOrCreate(
                ['student_id' => $student->id, 'module_id' => $module->id],
                [
                    'status' => 'needs_work',
                    'current_rung' => 1,
                    'current_streak' => 0,
                ]
            );
        }
    }
}
