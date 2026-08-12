<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A compact per-student learning profile (AG-08): a small array of derived tags
 * (style, misconceptions) injected into AI tutor prompts. Never transcripts, never PII.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->json('learning_profile')->nullable()->after('known_weak_areas');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn('learning_profile');
        });
    }
};
