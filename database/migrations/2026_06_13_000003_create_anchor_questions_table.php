<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Slice 1 — Migration 3 of 6: Anchor questions.
 *
 * The diagnostic item bank. Fields are pinned by admin_content.feature:
 * "options, correct index, difficulty, strand, and distractor notes, marked active".
 *
 *   subject / sea_section -> which paper area this anchor probes
 *   strand                -> finer-grained ladder key (e.g. "Number"); the item
 *                            walk adapts difficulty per strand
 *   difficulty            -> integer rung on the ladder (1 = easiest)
 *   options               -> JSON array of choice strings
 *   correct_index         -> 0-based index into options
 *   distractor_notes      -> JSON map of option index => misconception captured
 *                            when that wrong option is chosen
 *   is_active             -> only active anchors enter new diagnostic sessions
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('anchor_questions', function (Blueprint $table) {
            $table->id();
            $table->string('subject');
            $table->string('sea_section');
            $table->string('strand')->nullable();
            $table->unsignedTinyInteger('difficulty')->default(1);
            $table->text('prompt');
            $table->json('options');
            $table->unsignedTinyInteger('correct_index');
            $table->json('distractor_notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['subject', 'sea_section', 'strand', 'difficulty'], 'anchor_ladder_index');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('anchor_questions');
    }
};
