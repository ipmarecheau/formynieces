<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A student's response to a weekly writing prompt. Writing never enters the module
 * mastery model and never reduces to a grade — a submission carries a four-criterion
 * rubric profile and warm feedback only. `status` is 'pending' until scored (WR-03
 * graceful degradation queues scoring when the AI provider is unavailable).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('writing_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('writing_prompt_id')->constrained('writing_prompts')->cascadeOnDelete();
            $table->text('body');
            $table->string('status')->default('pending'); // pending | scored

            // Four-criterion rubric profile (nullable until scored).
            $table->unsignedTinyInteger('content_score')->nullable();
            $table->unsignedTinyInteger('language_score')->nullable();
            $table->unsignedTinyInteger('grammar_score')->nullable();
            $table->unsignedTinyInteger('organisation_score')->nullable();

            // Warm feedback: two things done well, one thing to try next time.
            $table->json('did_well')->nullable();
            $table->text('try_next')->nullable();

            $table->timestamp('scored_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('writing_submissions');
    }
};
