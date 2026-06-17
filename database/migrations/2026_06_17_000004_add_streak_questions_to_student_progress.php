<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student_progress', function (Blueprint $table) {
            // Question ids making up the CURRENT live streak, JSON array.
            // Cleared whenever the streak resets or a rung is cleared.
            $table->text('streak_question_ids')->nullable()->after('current_streak');
        });
    }

    public function down(): void
    {
        Schema::table('student_progress', function (Blueprint $table) {
            $table->dropColumn('streak_question_ids');
        });
    }
};