<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Deferred checks for delayed-feedback scenarios (e.g. a notification that fires later). The
 * tester chooses "remind me to check", and the system — knowing their timezone and the
 * scenario's expected notification time — emails them twice: just before the event to watch
 * for it, and after it to record whether it arrived. These columns carry that waiting state.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('verification_runs', function (Blueprint $table) {
            // The tester's IANA timezone (from their browser), so reminders land at the right
            // local moment relative to the scenario's expected notification time.
            $table->string('tester_timezone')->nullable()->after('tester_email');
            // When the notification is expected to fire (UTC). The two reminders hang off this.
            $table->timestamp('expected_at')->nullable()->after('result');
            // Idempotency stamps so each reminder is sent exactly once.
            $table->timestamp('first_reminder_sent_at')->nullable()->after('expected_at');
            $table->timestamp('second_reminder_sent_at')->nullable()->after('first_reminder_sent_at');

            // The sweep finds waiting checks whose reminder windows have opened.
            $table->index(['result', 'expected_at']);
        });
    }

    public function down(): void
    {
        Schema::table('verification_runs', function (Blueprint $table) {
            $table->dropIndex(['result', 'expected_at']);
            $table->dropColumn([
                'tester_timezone',
                'expected_at',
                'first_reminder_sent_at',
                'second_reminder_sent_at',
            ]);
        });
    }
};
