<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Per-student, per-day active guided-learning time (AG-05..07). One row per student
 * per day; practice never accrues here — only lessons, tutorials, clarify chat and
 * re-teach do, and only while she is actively engaged.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_guided_time', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->date('day');
            $table->unsignedInteger('active_seconds')->default(0);
            $table->timestamps();

            $table->unique(['student_id', 'day']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_guided_time');
    }
};
