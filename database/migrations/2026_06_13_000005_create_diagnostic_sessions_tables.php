<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Slice 1 — Migration 5 of 6: Diagnostic sessions and responses.
 *
 * diagnostic_sessions: one row per attempt by a student. Persists the computed
 * item plan (JSON) so an interrupted session can resume (diagnostic.feature:
 * "an interrupted session resumes"). status is in_progress | completed.
 *
 * diagnostic_responses: one row per answered anchor. Records the chosen option,
 * correctness, and — on a wrong answer — the misconception encoded by the
 * chosen distractor (diagnostic.feature: "the misconception encoded by her
 * chosen distractor is recorded").
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('diagnostic_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')
                ->constrained('users')
                ->cascadeOnDelete();
            $table->string('status')->default('in_progress');
            $table->json('item_plan')->nullable();
            $table->unsignedSmallInteger('current_item')->default(0);
            $table->json('writing_sample')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['student_id', 'status']);
        });

        Schema::create('diagnostic_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('diagnostic_session_id')
                ->constrained('diagnostic_sessions')
                ->cascadeOnDelete();
            $table->foreignId('anchor_question_id')
                ->constrained('anchor_questions')
                ->cascadeOnDelete();
            $table->unsignedTinyInteger('chosen_index')->nullable();
            $table->boolean('is_correct')->nullable();
            $table->string('misconception')->nullable();
            $table->timestamps();

            $table->index('diagnostic_session_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('diagnostic_responses');
        Schema::dropIfExists('diagnostic_sessions');
    }
};
