<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A stable per-question reference from an external bank (e.g. the Moodle question
 * name "Q01 · D1 · v1 — Addition"). Lets the importer upsert idempotently — a
 * re-uploaded file updates the matching question instead of duplicating it.
 * Nullable so hand-seeded questions are unaffected; SQLite allows many NULLs
 * under a unique index.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('practice_questions', function (Blueprint $table) {
            $table->string('source_ref')->nullable()->unique()->after('is_active');
        });
    }

    public function down(): void
    {
        Schema::table('practice_questions', function (Blueprint $table) {
            $table->dropUnique(['source_ref']);
            $table->dropColumn('source_ref');
        });
    }
};
