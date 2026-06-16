<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Widen student_progress.status to the diagnostic engine's four-status
 * vocabulary (D6):
 *   not_started        — never touched by the diagnostic (default)
 *   needs_work         — directly tested and answered incorrectly
 *   inferred_mastered  — implied mastered via the prerequisite graph
 *   mastered           — directly tested and answered correctly
 *
 * The original CHECK allowed only ('not_started','diagnostic_passed','mastered').
 * 'diagnostic_passed' is retained for backward compatibility with existing rows.
 *
 * Uses enum(...)->change(), which on Laravel 13 needs no doctrine/dbal. On
 * SQLite this rebuilds the table to swap the CHECK constraint; data is preserved.
 */
return new class extends Migration
{
    private array $newStatuses = [
        'not_started',
        'needs_work',
        'inferred_mastered',
        'mastered',
        'diagnostic_passed', // legacy
    ];

    private array $oldStatuses = [
        'not_started',
        'diagnostic_passed',
        'mastered',
    ];

    public function up(): void
    {
        Schema::table('student_progress', function (Blueprint $table) {
            $table->enum('status', $this->newStatuses)
                ->default('not_started')
                ->change();
        });
    }

    public function down(): void
    {
        // Fold new statuses back to legacy values BEFORE tightening the CHECK,
        // or the constraint rebuild would reject existing rows.
        DB::table('student_progress')->where('status', 'needs_work')
            ->update(['status' => 'not_started']);
        DB::table('student_progress')->where('status', 'inferred_mastered')
            ->update(['status' => 'diagnostic_passed']);

        Schema::table('student_progress', function (Blueprint $table) {
            $table->enum('status', $this->oldStatuses)
                ->default('not_started')
                ->change();
        });
    }
};
