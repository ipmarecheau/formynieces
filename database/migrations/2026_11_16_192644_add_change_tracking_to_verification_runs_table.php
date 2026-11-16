<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Lets a verdict know what it was given against, so we can tell when a scenario has changed
 * since the tester last looked and ask them to re-test. We stamp the fingerprint of the BDD
 * definition (spec_hash) and of the files that implement it (impl_hash) at verdict time, plus
 * the git commit then, so the change can be explained from version control. change_notified_hash
 * records the last spec|impl combination we emailed about, so a tester is nudged once per change.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('verification_runs', function (Blueprint $table) {
            $table->string('spec_hash', 12)->nullable()->after('result');
            $table->string('impl_hash', 12)->nullable()->after('spec_hash');
            $table->string('verified_commit', 40)->nullable()->after('impl_hash');
            $table->string('change_notified_hash', 25)->nullable()->after('verified_commit');
        });
    }

    public function down(): void
    {
        Schema::table('verification_runs', function (Blueprint $table) {
            $table->dropColumn(['spec_hash', 'impl_hash', 'verified_commit', 'change_notified_hash']);
        });
    }
};
