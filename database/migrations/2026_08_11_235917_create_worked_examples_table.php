<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Cached worked examples for the tutorial stage. The first time a D1 question is
 * used in a tutorial, Smooth (the LLM) generates a step-by-step worked example; it
 * is cached here (one per question) and reused for other students who have not yet
 * seen that question — so the LLM cost is paid once per question, not per student.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('worked_examples', function (Blueprint $table) {
            $table->id();
            $table->foreignId('practice_question_id')->unique()->constrained()->cascadeOnDelete();
            $table->json('steps');                 // ordered step strings, revealed one at a time
            $table->string('source')->default('llm'); // llm | explanation (fallback)
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('worked_examples');
    }
};
