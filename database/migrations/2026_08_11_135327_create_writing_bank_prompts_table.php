<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The writing-prompt bank: past-paper-style essay prompts, each with its own
 * marking rubric, feeding the parallel writing track. Keyed by genre + difficulty;
 * `source_ref` (the Moodle question name) makes re-import idempotent.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('writing_bank_prompts', function (Blueprint $table) {
            $table->id();
            $table->string('source_ref')->unique();
            $table->string('genre');            // narrative | report
            $table->string('sub_genre');        // e.g. "Story Including a Given Line"
            $table->foreignId('module_id')->nullable()->constrained('syllabus_modules')->nullOnDelete();
            $table->unsignedTinyInteger('difficulty'); // 1 easy · 2 medium · 3 hard
            $table->string('title');
            $table->text('prompt');
            $table->json('rubric')->nullable();      // parsed RUBRIC_JSON (criteria + max)
            $table->text('rubric_html')->nullable(); // full grader table, for display/audit
            $table->unsignedSmallInteger('marks')->default(20);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['genre', 'difficulty', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('writing_bank_prompts');
    }
};
