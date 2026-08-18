<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Add 'mastered_review' to student_progress.status (LL-17): a mastered level whose
 * maintenance grace passed without a re-mastery decays to review — no longer counted
 * as mastered, so it becomes eligible for a future weekly target again.
 */
return new class extends Migration
{
    private array $withReview = [
        'not_started',
        'needs_work',
        'inferred_mastered',
        'mastered',
        'mastered_review',
        'diagnostic_passed', // legacy
    ];

    private array $withoutReview = [
        'not_started',
        'needs_work',
        'inferred_mastered',
        'mastered',
        'diagnostic_passed', // legacy
    ];

    public function up(): void
    {
        Schema::table('student_progress', function (Blueprint $table) {
            $table->enum('status', $this->withReview)
                ->default('not_started')
                ->change();
        });
    }

    public function down(): void
    {
        // Fold decayed levels back to mastered before tightening the CHECK.
        DB::table('student_progress')->where('status', 'mastered_review')
            ->update(['status' => 'mastered']);

        Schema::table('student_progress', function (Blueprint $table) {
            $table->enum('status', $this->withoutReview)
                ->default('not_started')
                ->change();
        });
    }
};
