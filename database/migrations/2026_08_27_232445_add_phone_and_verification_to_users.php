<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            // Guardian phone (E.164) and its WhatsApp/SMS verification stamp.
            $table->string('phone')->nullable()->after('email');
            $table->timestamp('phone_verified_at')->nullable()->after('email_verified_at');

            // The 6-digit code companion to Breeze's signed email link — stored
            // hashed, with a short expiry. Either the link or the code verifies.
            $table->string('email_verification_code')->nullable();
            $table->timestamp('email_verification_code_expires_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn([
                'phone',
                'phone_verified_at',
                'email_verification_code',
                'email_verification_code_expires_at',
            ]);
        });
    }
};
