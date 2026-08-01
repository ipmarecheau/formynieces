<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The weekly writing prompt for the parallel writing track. One shared prompt per
 * study week (Monday-anchored), adapted from past Creative Writing papers.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('writing_prompts', function (Blueprint $table) {
            $table->id();
            $table->date('week_start_date')->unique();
            $table->string('title');
            $table->text('prompt');
            $table->string('type')->default('narrative'); // narrative, expository, descriptive…
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('writing_prompts');
    }
};
