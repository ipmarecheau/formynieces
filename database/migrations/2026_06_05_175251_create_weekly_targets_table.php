<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('weekly_targets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')
                  ->constrained('users')
                  ->cascadeOnDelete();
            $table->foreignId('module_id')
                  ->constrained('syllabus_modules')
                  ->cascadeOnDelete();
            $table->date('week_start_date');
            $table->boolean('is_completed')->default(false);
            $table->timestamps();

            $table->unique(['student_id', 'week_start_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('weekly_targets');
    }
};