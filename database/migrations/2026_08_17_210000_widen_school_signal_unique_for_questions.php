<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * SJ-13 — per-question module signals: several questions on one paper can
     * share a strand, so uniqueness becomes (entry, strand, module).
     */
    public function up(): void
    {
        Schema::table('school_strand_signals', function (Blueprint $table) {
            $table->dropUnique(['school_journal_entry_id', 'strand']);
        });

        Schema::table('school_strand_signals', function (Blueprint $table) {
            $table->unique(['school_journal_entry_id', 'strand', 'syllabus_module_id'], 'sj_signals_entry_strand_module_unique');
        });
    }

    public function down(): void
    {
        Schema::table('school_strand_signals', function (Blueprint $table) {
            $table->dropUnique('sj_signals_entry_strand_module_unique');
        });

        Schema::table('school_strand_signals', function (Blueprint $table) {
            $table->unique(['school_journal_entry_id', 'strand']);
        });
    }
};
