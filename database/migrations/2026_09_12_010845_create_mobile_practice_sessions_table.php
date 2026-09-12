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
        Schema::create('mobile_practice_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('module_id')->constrained('syllabus_modules')->cascadeOnDelete();
            $table->json('question_ids');            // ordered ids served this session
            $table->unsignedInteger('position')->default(0); // how many answered
            $table->json('answers')->default('[]');  // [{question_id, correct}]
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mobile_practice_sessions');
    }
};
