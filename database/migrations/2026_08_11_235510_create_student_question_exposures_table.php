<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The per-student question-exposure ledger. Every question served to a student —
 * in the diagnostic, tutorial, practice, or check — records its content hash here,
 * so selection never repeats a question she has already seen. Keyed by content
 * hash (not id) so the same question dedupes across banks. `seen_count` +
 * `updated_at` support the maintenance-phase recycle (least-recently-seen first)
 * once a module's pool is exhausted.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_question_exposures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->string('content_hash');
            $table->string('context')->nullable(); // diagnostic | tutorial | practice | check
            $table->unsignedInteger('seen_count')->default(1);
            $table->timestamps();

            $table->unique(['student_id', 'content_hash']);
            $table->index(['student_id', 'updated_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_question_exposures');
    }
};
