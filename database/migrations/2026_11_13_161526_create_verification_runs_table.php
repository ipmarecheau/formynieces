<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Human test-drive results. Each row is one tester's verdict on one scenario during a
 * guided /user-testing walkthrough. This is EVIDENCE Isaac reviews — it never writes the
 * git-tracked specs ledger directly; he still records specs:verify himself (G5).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('verification_runs', function (Blueprint $table) {
            $table->id();
            $table->string('tester_name');
            $table->string('feature');            // feature code, e.g. "HC"
            $table->string('scenario_id');        // e.g. "HC-02"
            $table->string('result');             // worked | issue | skipped
            $table->text('notes')->nullable();
            $table->string('screenshot_path')->nullable();
            $table->timestamps();

            $table->index(['scenario_id', 'result']);
            $table->index('feature');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('verification_runs');
    }
};
