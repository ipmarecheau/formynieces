<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Child-safety escalation flags (AG-15). When a child's message to an AI tutor is
 * classified into a concerning category (self-harm, abuse, distress), a flag is recorded
 * for her guardian and an admin to follow up — care, not just a block.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('safety_flags', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->string('category');       // moderation category code (e.g. self-harm)
            $table->text('excerpt')->nullable(); // a short, truncated context snippet
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('safety_flags');
    }
};
