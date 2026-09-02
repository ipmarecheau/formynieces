<?php

namespace Database\Seeders;

use App\Models\StudentJourney;
use App\Models\SyllabusModule;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * A free-plan guardian + child for verifying the free-tier gating in the browser
 * (features.free_tier on). Deliberately minimal — the point is a working, navigable
 * free account, not a rich mid-journey.
 */
class FreeTierDemoSeeder extends Seeder
{
    public function run(): void
    {
        $guardian = User::updateOrCreate(
            ['email' => 'freetier-guardian@smoothseas.test'],
            [
                'name' => 'Free Guardian',
                'password' => 'smoothseas',
                'role' => 'guardian',
                'plan' => 'free',
                'email_verified_at' => now(),
                'age_attested_at' => now(),
            ],
        );

        $student = User::updateOrCreate(
            ['email' => 'freetier-student@smoothseas.test'],
            [
                'name' => 'Free Student',
                'password' => 'smoothseas',
                'role' => 'student',
                'plan' => 'free',
                'parent_id' => $guardian->id,
                'onboarding_completed_at' => now(),
                'target_sea_year' => now()->addYear()->year,
                'email_verified_at' => now(),
            ],
        );

        StudentJourney::updateOrCreate(
            ['student_id' => $student->id],
            [
                'journey_start' => now()->toDateString(),
                'exam_date' => now()->addYear()->setDate(now()->year + 1, 4, 1)->toDateString(),
            ],
        );

        // Leave a module in "needs_work" via a mastered spread so the map has life,
        // reusing whatever modules the environment already has seeded.
        $module = SyllabusModule::query()->where('subject', 'Math')->orderBy('sequence_order')->first();
        $this->command?->info('Free-tier demo module id: '.($module?->id ?? 'none'));
        $this->command?->info('Free guardian: freetier-guardian@smoothseas.test / smoothseas');
        $this->command?->info('Free student:  freetier-student@smoothseas.test / smoothseas');
    }
}
