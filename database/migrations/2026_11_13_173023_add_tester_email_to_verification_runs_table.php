<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Identify testers by email so their verdicts persist across devices and sessions, and keep
 * one verdict per tester per scenario (they can go back and change their answer).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('verification_runs', function (Blueprint $table) {
            $table->string('tester_email')->after('id')->default('');
            $table->unique(['tester_email', 'scenario_id']);
        });
    }

    public function down(): void
    {
        Schema::table('verification_runs', function (Blueprint $table) {
            $table->dropUnique(['tester_email', 'scenario_id']);
            $table->dropColumn('tester_email');
        });
    }
};
