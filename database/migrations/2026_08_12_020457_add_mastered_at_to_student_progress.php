<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * mastered_at anchors the two-week maintenance window (LL-23/24) and its decay to
 * mastered_review (LL-17): due = mastered_at + 14d, grace end = +19d. Set on every
 * mastery transition (the practice climb and the test-out), reset on each re-master.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student_progress', function (Blueprint $table): void {
            $table->timestamp('mastered_at')->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('student_progress', function (Blueprint $table): void {
            $table->dropColumn('mastered_at');
        });
    }
};
