<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The authored/imported daily-reading pool (DR-06). Level-keyed passages with
     * their comprehension questions; vocabulary words live in vocabulary_words.
     */
    public function up(): void
    {
        Schema::create('reading_passages', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('body');
            $table->unsignedTinyInteger('reading_level');   // level band the passage matches
            $table->unsignedInteger('word_count');          // for pace (words-per-minute) maths
            // [{ prompt, type: 'mc'|'written', options?: [], correct_index?: int }]
            $table->json('questions');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('reading_level');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reading_passages');
    }
};
