<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * A digitised classroom assessment (SJ). A student or guardian uploads a photo
     * (SJ-01); the OcrService fills the structured fields (SJ-07), with low-confidence
     * fields flagged for confirmation (SJ-02). Guardian/system layer only.
     */
    public function up(): void
    {
        Schema::create('school_journal_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->string('uploaded_by');                  // 'student' | 'guardian'
            $table->string('image_path');
            $table->date('assessment_date')->nullable();
            $table->string('term')->nullable();
            $table->string('subject')->nullable();
            $table->string('strand')->nullable();
            $table->string('assessment_type')->nullable();
            $table->string('score')->nullable();            // as written, e.g. "18/20"
            $table->text('teacher_comment')->nullable();
            $table->text('ocr_text')->nullable();
            $table->json('ocr_confidence')->nullable();     // {field: 0.0–1.0}
            $table->string('digitisation_status')->default('pending'); // pending|digitised|confirmed
            $table->timestamps();

            $table->index(['student_id', 'assessment_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('school_journal_entries');
    }
};
