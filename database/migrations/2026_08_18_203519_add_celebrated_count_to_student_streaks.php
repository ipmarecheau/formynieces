<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * CE-04 — the highest streak length already celebrated, so a streak-milestone
 * celebration plays once when she next opens her Voyage and never re-fires.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student_streaks', function (Blueprint $table) {
            $table->unsignedInteger('celebrated_count')->default(0)->after('previous_count');
        });
    }

    public function down(): void
    {
        Schema::table('student_streaks', function (Blueprint $table) {
            $table->dropColumn('celebrated_count');
        });
    }
};
