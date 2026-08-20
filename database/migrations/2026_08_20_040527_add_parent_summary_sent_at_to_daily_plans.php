<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * PN-01 — guards the once-per-day parent summary email, so a student's guardian
 * is notified at most once about a given day (inactivity or completion).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('daily_plans', function (Blueprint $table) {
            $table->timestamp('parent_summary_sent_at')->nullable()->after('completed_at');
        });
    }

    public function down(): void
    {
        Schema::table('daily_plans', function (Blueprint $table) {
            $table->dropColumn('parent_summary_sent_at');
        });
    }
};
