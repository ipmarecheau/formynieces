<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\User;
use App\Models\PracticeQuestion;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('practice_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(User::class, 'student_id')
                  ->constrained('users')
                  ->cascadeOnDelete();
            $table->foreignIdFor(PracticeQuestion::class, 'practice_question_id')
                  ->constrained('practice_questions')
                  ->cascadeOnDelete();
            $table->integer('module_id');   // denormalised for fast per-module queries
            $table->integer('difficulty');  // the rung this attempt was at, captured at answer time
            $table->boolean('is_correct');
            $table->timestamps();

            $table->index(['student_id', 'module_id', 'created_at'], 'attempt_history_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('practice_attempts');
    }
};