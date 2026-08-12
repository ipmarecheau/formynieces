<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Per-student, per-month LLM budget ledger (AG-01..04). One row per student per
 * calendar month (period = 'YYYY-MM'); cost accumulates from real provider usage.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_llm_usage', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->string('period', 7);   // YYYY-MM
            $table->unsignedBigInteger('input_tokens')->default(0);
            $table->unsignedBigInteger('output_tokens')->default(0);
            $table->decimal('cost_usd', 10, 6)->default(0);
            $table->timestamps();

            $table->unique(['student_id', 'period']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_llm_usage');
    }
};
