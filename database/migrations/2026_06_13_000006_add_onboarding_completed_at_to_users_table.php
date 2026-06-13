<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Slice 1 — Migration 6 of 6: onboarding_completed_at on users.
 *
 * Nullable timestamp set when the diagnostic reveal completes
 * (roadmap_reveal.feature). Drives login routing: a student with a null value
 * goes to the diagnostic intro; a non-null value goes to the populated
 * dashboard (guardian_onboarding.feature / roadmap_reveal.feature).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('onboarding_completed_at')->nullable()->after('parent_id');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('onboarding_completed_at');
        });
    }
};
