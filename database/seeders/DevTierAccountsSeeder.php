<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Dev-only test accounts: a guardian + student on the FREE tier and on the PAID (premium)
 * tier, for exercising free-vs-paid gating. Idempotent (upsert by email).
 *
 * NOT registered in DatabaseSeeder — it must never run on prod. Run manually on dev:
 *     php artisan db:seed --class=DevTierAccountsSeeder
 *
 * All passwords are "password". Students sign in at /go with their email; guardians at /login.
 * NOTE: the free/paid DIFFERENCE only shows when features.free_tier is enabled (dev default is
 * off → everyone has full access). See the Native-apps / Environments docs.
 */
class DevTierAccountsSeeder extends Seeder
{
    public function run(): void
    {
        $this->pair('free', 'Free', 'free', null);
        $this->pair('paid', 'Paid', 'premium', now());
    }

    private function pair(string $slug, string $label, string $plan, ?\DateTimeInterface $firstBillAt): void
    {
        $guardian = User::updateOrCreate(
            ['email' => "{$slug}-parent@smoothseas.test"],
            [
                'name' => "{$label} Parent",
                'password' => Hash::make('password'),
                'role' => 'guardian',
                'email_verified_at' => now(),
            ],
        );
        $guardian->plan = $plan;                 // free | premium
        $guardian->first_bill_at = $firstBillAt; // set for the paid tier's billing display
        $guardian->save();

        $child = User::updateOrCreate(
            ['email' => "{$slug}-child@smoothseas.test"],
            [
                'name' => "{$label} Child",
                'password' => Hash::make('password'),
                'role' => 'student',
                'parent_id' => $guardian->id,
                'target_sea_year' => now()->year + 1,
                'onboarding_completed_at' => now(), // land on the Voyage, not the diagnostic
            ],
        );
        $child->child_password_enc = 'password'; // guardian can reveal/reset it in the portal
        $child->save();

        $this->command?->info("{$label} tier: {$slug}-parent@smoothseas.test / {$slug}-child@smoothseas.test (plan={$plan})");
    }
}
