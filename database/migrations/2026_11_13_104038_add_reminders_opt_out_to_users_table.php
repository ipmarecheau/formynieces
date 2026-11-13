<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // LR-06: a guardian can switch on-device reminders off for a child.
            // Quiet hours reuse the existing quiet_hours_start/end columns.
            $table->boolean('reminders_opt_out')->default(false);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('reminders_opt_out');
        });
    }
};
