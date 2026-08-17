<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * SJ-08 — corroborating/weakness signals a confirmed school assessment writes
     * for a strand, consumed by the honest layer (never auto-mastery, SJ-06).
     * SJ-05 — the day's gentle focus hint on the daily plan.
     */
    public function up(): void
    {
        Schema::create('school_strand_signals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('school_journal_entry_id')->constrained('school_journal_entries')->cascadeOnDelete();
            $table->string('strand');
            $table->string('direction'); // corroborates | weakens
            $table->decimal('strength', 3, 2)->default(0.50);
            $table->timestamps();
            $table->unique(['school_journal_entry_id', 'strand']);
        });

        Schema::table('daily_plans', function (Blueprint $table) {
            $table->string('focus_hint')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('school_strand_signals');
        Schema::table('daily_plans', function (Blueprint $table) {
            $table->dropColumn('focus_hint');
        });
    }
};
