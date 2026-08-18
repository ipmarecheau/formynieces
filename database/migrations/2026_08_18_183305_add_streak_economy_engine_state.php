<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * State for the streak-economy engine (SE-03/07/08/09/10/11/12):
 *  - student_streaks.previous_count — the count a streak held just before it
 *    reset, so a Lifebuoy can restore it (SE-11).
 *  - streak_shields — per-student protection: the three starter days (SE-03)
 *    and an Anchor freeze date (SE-08).
 *  - streak_banks — per-subject days banked ahead by accelerating (SE-09/10).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student_streaks', function (Blueprint $table) {
            $table->unsignedInteger('previous_count')->nullable()->after('count');
        });

        Schema::create('streak_shields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->unsignedTinyInteger('starter_protection_remaining')->default(3);
            $table->date('frozen_on')->nullable();
            $table->timestamps();
        });

        Schema::create('streak_banks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->string('subject');
            $table->unsignedTinyInteger('days_ahead')->default(0);
            $table->timestamps();
            $table->unique(['student_id', 'subject']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('streak_banks');
        Schema::dropIfExists('streak_shields');
        Schema::table('student_streaks', function (Blueprint $table) {
            $table->dropColumn('previous_count');
        });
    }
};
