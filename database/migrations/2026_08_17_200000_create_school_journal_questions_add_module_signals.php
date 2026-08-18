<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * SJ-11..13 — the per-question breakdown of a digitised assessment, aligned
     * to the syllabus, with clipped question images and reasoning notes.
     */
    public function up(): void
    {
        Schema::create('school_journal_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_journal_entry_id')->constrained('school_journal_entries')->cascadeOnDelete();
            $table->unsignedInteger('number')->nullable();
            $table->text('prompt')->nullable();
            $table->text('student_answer')->nullable();
            $table->text('correct_answer')->nullable();
            $table->boolean('is_correct')->nullable();
            $table->foreignId('syllabus_module_id')->nullable()->constrained('syllabus_modules')->nullOnDelete();
            $table->string('topic_label')->nullable();
            $table->decimal('topic_confidence', 3, 2)->default(0);
            $table->text('reasoning_note')->nullable();
            $table->string('clip_path')->nullable();
            $table->json('clip_box')->nullable();
            $table->timestamps();
        });

        Schema::table('school_strand_signals', function (Blueprint $table) {
            $table->foreignId('syllabus_module_id')->nullable()->constrained('syllabus_modules')->nullOnDelete()->after('strand');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('school_journal_questions');
        Schema::table('school_strand_signals', function (Blueprint $table) {
            $table->dropConstrainedForeignId('syllabus_module_id');
        });
    }
};
