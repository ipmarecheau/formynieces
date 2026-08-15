<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The Captain's Locker: how many of each protective reward a student holds.
     * One row per (student, type). type ∈ shore_leave|anchor|tailwind|lifebuoy.
     * `source` records how the most recent grant was earned (ahead|milestone|
     * guardian|xp) for light attribution; the running balance is `quantity`.
     */
    public function up(): void
    {
        Schema::create('streak_rewards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->string('type');
            $table->unsignedInteger('quantity')->default(0);
            $table->string('source')->nullable();
            $table->timestamps();

            $table->unique(['student_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('streak_rewards');
    }
};
