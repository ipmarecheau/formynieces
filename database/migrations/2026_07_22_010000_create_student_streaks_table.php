<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_streaks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')
                ->constrained('users')
                ->cascadeOnDelete();
            $table->string('type');
            $table->unsignedInteger('count')->default(0);
            $table->date('last_activity_date')->nullable();
            $table->timestamps();

            $table->unique(['student_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_streaks');
    }
};
