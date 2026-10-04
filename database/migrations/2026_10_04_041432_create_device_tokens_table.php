<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Device push tokens for the native apps (Capacitor). One row per device registration;
 * the same token is upserted so re-registration never duplicates. Used to send FCM/APNs
 * push (streak nudges, progress reminders).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('device_tokens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('token', 512)->unique();
            $table->string('platform', 20)->nullable();  // ios | android | web
            $table->string('app', 20)->nullable();        // parent | child
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'app']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('device_tokens');
    }
};
