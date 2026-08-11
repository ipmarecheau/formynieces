<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Persistent per-student record of which Smooth guides she has already dismissed,
 * so a screen's how-to shows once (on first visit) and never nags again — surviving
 * across her devices (SG-01, SG-02).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->json('seen_guides')->nullable()->after('known_weak_areas');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('seen_guides');
        });
    }
};
