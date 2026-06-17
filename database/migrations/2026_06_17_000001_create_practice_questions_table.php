<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\SyllabusModule;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('practice_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(SyllabusModule::class, 'module_id')
                  ->constrained('syllabus_modules')
                  ->cascadeOnDelete();
            $table->string('subject');
            $table->string('sea_section');
            $table->string('strand')->nullable();
            $table->integer('difficulty')->default(1);   // 1 easy / 2 medium / 3 hard — climb
            $table->integer('sequence_order')->nullable(); // optional author-pinned order
            $table->text('prompt');
            $table->text('options');                       // JSON array, mirrors anchor_questions
            $table->integer('correct_index');
            $table->text('hint')->nullable();              // shown on request, before answering
            $table->text('explanation')->nullable();       // shown after answering — the teaching moment
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['module_id', 'difficulty', 'sequence_order'], 'practice_ladder_index');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('practice_questions');
    }
};