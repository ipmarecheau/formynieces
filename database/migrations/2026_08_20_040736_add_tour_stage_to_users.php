<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * TR-07 — tracks how far through the interactive cross-page tour a student is,
 * so each screen (overworld → island → lesson) resumes the right segment.
 * null = not in a tour; 'overworld' | 'island' | 'lesson' | 'done'.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('tour_stage')->nullable()->after('welcomed_at');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('tour_stage');
        });
    }
};
