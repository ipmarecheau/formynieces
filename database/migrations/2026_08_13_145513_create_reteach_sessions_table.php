<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * A re-teach (remediation) session for a student on a module — the AI-assisted recovery she is
     * pulled into after struggling in practice (LL-14…16, LL-22). Owns the mode's lifecycle and,
     * via `completed_at`, marks the boundary the miss-counters read from (they reset on entry).
     */
    public function up(): void
    {
        Schema::create('reteach_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('module_id')->constrained('syllabus_modules')->cascadeOnDelete();
            // Why she was pulled in: 'streak' (2-in-a-row at D3/D5) or 'window' (5 of last 7).
            $table->string('trigger');
            $table->unsignedTinyInteger('correct_count')->default(0);
            $table->timestamp('started_at');
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['student_id', 'module_id', 'completed_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reteach_sessions');
    }
};
