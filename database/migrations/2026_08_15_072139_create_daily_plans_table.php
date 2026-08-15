<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The day's Captain's Brief: the minimum duties for one student on one day,
     * tracked as done/not-done flags. The reading/vocabulary/writing engines flip
     * their flags as they land; the map flag is flipped by map activity today.
     */
    public function up(): void
    {
        Schema::create('daily_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->date('date');
            $table->boolean('is_writing_day')->default(false);
            // {vocabulary: bool, reading: bool, map: bool, writing: bool|null}
            // A duty absent/null = not required today (e.g. writing on a non-writing day).
            $table->json('duties');
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->unique(['student_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_plans');
    }
};
