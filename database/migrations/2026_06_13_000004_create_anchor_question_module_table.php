<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Slice 1 — Migration 4 of 6: Anchor-to-module certification pivot.
 *
 * One anchor question certifies mastery of several modules (the prerequisite
 * chain it sits at the top of). diagnostic.feature: answering an anchor marks
 * "the answered anchor's prerequisite modules" as inferred mastered, so the
 * relationship is many-to-many by design, not one-to-one.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('anchor_question_module', function (Blueprint $table) {
            $table->id();
            $table->foreignId('anchor_question_id')
                ->constrained('anchor_questions')
                ->cascadeOnDelete();
            $table->foreignId('module_id')
                ->constrained('syllabus_modules')
                ->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['anchor_question_id', 'module_id'], 'anchor_module_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('anchor_question_module');
    }
};
