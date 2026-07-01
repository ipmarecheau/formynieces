<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('weekly_targets', function (Blueprint $table) {
            // Was: one module per (student, week). Now: many modules per week.
            $table->dropUnique('weekly_targets_student_id_week_start_date_unique');

            // Prevent the same module appearing twice in one student's week.
            $table->unique(['student_id', 'module_id', 'week_start_date'], 'weekly_targets_student_module_week_unique');
        });
    }

    public function down(): void
    {
        Schema::table('weekly_targets', function (Blueprint $table) {
            $table->dropUnique('weekly_targets_student_module_week_unique');
            $table->unique(['student_id', 'week_start_date'], 'weekly_targets_student_id_week_start_date_unique');
        });
    }
};
