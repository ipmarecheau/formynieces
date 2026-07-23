<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // When the diagnostic cleared a strand the guardian flagged, her
            // onboarding waits until she reconciles. This records when she did
            // (or when the 3-day auto-proceed resolved it). [RR-04/RR-10]
            $table->timestamp('guardian_reconciled_at')->nullable()->after('onboarding_completed_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('guardian_reconciled_at');
        });
    }
};
