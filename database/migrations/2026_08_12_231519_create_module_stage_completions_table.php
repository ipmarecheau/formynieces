<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('module_stage_completions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('module_id')->constrained('syllabus_modules')->cascadeOnDelete();
            // The learning stage completed at least once: 'lesson' or 'tutorial' (worked examples).
            $table->string('stage');
            $table->timestamp('completed_at');
            $table->timestamps();

            $table->unique(['student_id', 'module_id', 'stage']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('module_stage_completions');
    }
};
