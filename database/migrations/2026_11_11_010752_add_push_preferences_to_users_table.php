<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // When true, non-critical (standard) pushes are suppressed for this user.
            $table->boolean('push_opt_out')->default(false);
            // Quiet-hours window in "HH:MM" (app timezone). Standard pushes inside it are suppressed.
            $table->string('quiet_hours_start', 5)->nullable();
            $table->string('quiet_hours_end', 5)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['push_opt_out', 'quiet_hours_start', 'quiet_hours_end']);
        });
    }
};
