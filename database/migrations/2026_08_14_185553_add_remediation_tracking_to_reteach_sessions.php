<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reteach_sessions', function (Blueprint $table): void {
            // How many same-rule remediation cycles she has been through on the current block (LL-26/27).
            $table->unsignedTinyInteger('remediation_cycles')->default(0)->after('correct_count');
            // Set when a block survives three cycles: the lesson is left "in progress" (LL-27). Phase 2
            // reads this to resurface the lesson daily + notify the parent.
            $table->timestamp('left_in_progress_at')->nullable()->after('remediation_cycles');
        });
    }

    public function down(): void
    {
        Schema::table('reteach_sessions', function (Blueprint $table): void {
            $table->dropColumn(['remediation_cycles', 'left_in_progress_at']);
        });
    }
};
