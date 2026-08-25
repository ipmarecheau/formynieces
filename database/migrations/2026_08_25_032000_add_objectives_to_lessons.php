<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A lesson declares which SEA syllabus objectives (by module code) it teaches directly and
 * reinforces indirectly. Single source for the in-lesson objective badge and the read-only
 * Syllabus coverage page (lesson-development standard §6).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lessons', function (Blueprint $table) {
            $table->json('objectives_direct')->nullable()->after('is_published');
            $table->json('objectives_indirect')->nullable()->after('objectives_direct');
        });
    }

    public function down(): void
    {
        Schema::table('lessons', function (Blueprint $table) {
            $table->dropColumn(['objectives_direct', 'objectives_indirect']);
        });
    }
};
