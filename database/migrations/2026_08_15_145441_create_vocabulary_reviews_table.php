<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Per-student spaced-repetition state for a vocabulary word (DV-03). A word she
     * keeps getting right returns less often; one she misses returns sooner.
     */
    public function up(): void
    {
        Schema::create('vocabulary_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('word_id')->constrained('vocabulary_words')->cascadeOnDelete();
            $table->unsignedSmallInteger('interval_days')->default(1);
            $table->unsignedSmallInteger('correct_streak')->default(0);
            $table->date('due_at');
            $table->date('last_seen_at')->nullable();
            $table->timestamps();

            $table->unique(['student_id', 'word_id']);
            $table->index(['student_id', 'due_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vocabulary_reviews');
    }
};
