<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')
                  ->constrained('users')
                  ->cascadeOnDelete();
            $table->foreignId('module_id')
                  ->constrained('syllabus_modules')
                  ->cascadeOnDelete();
            $table->enum('status', ['not_started', 'diagnostic_passed', 'mastered'])
                  ->default('not_started');
            $table->unsignedTinyInteger('score')->nullable();
            $table->timestamps();

            $table->unique(['student_id', 'module_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_progress');
    }
};