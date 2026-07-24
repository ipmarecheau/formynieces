<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * An audit log of guardian pause spans. One row per pause; resumed_at is
     * null while the pause is active. Total paused time is excluded from the
     * pacing clock so a pause never counts against the student.
     */
    public function up(): void
    {
        Schema::create('student_pauses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->timestamp('paused_at');
            $table->timestamp('resumed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_pauses');
    }
};
