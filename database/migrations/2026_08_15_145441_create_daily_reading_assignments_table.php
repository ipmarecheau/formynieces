<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * One served passage per student per day (DR). Resumable; comprehension is
     * scored and kept (DR-07); reading pace is derived from started→completed time
     * over the passage word count (DR-08). The score/pace are honest-layer numbers,
     * never a grade shown to the child.
     */
    public function up(): void
    {
        Schema::create('daily_reading_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('passage_id')->constrained('reading_passages');
            $table->date('date');
            $table->json('answers')->nullable();           // per-question answers so far
            $table->unsignedInteger('resume_position')->default(0); // words read / scroll offset
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->unsignedTinyInteger('comprehension_score')->nullable(); // 0–100
            $table->unsignedSmallInteger('words_per_minute')->nullable();
            $table->timestamps();

            $table->unique(['student_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_reading_assignments');
    }
};
