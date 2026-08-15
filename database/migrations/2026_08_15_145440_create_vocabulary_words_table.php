<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Words drawn from a passage, met in the sentence they appeared in (DV-01/02).
     */
    public function up(): void
    {
        Schema::create('vocabulary_words', function (Blueprint $table) {
            $table->id();
            $table->foreignId('passage_id')->constrained('reading_passages')->cascadeOnDelete();
            $table->string('word');
            $table->string('definition');
            $table->text('context_sentence');
            $table->timestamps();

            $table->index('passage_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vocabulary_words');
    }
};
