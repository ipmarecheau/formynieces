<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            // Which social provider a guardian signed up / linked with, and the id
            // that provider knows them by. Null for email/password accounts.
            $table->string('social_provider')->nullable()->after('password');
            $table->string('social_id')->nullable()->after('social_provider');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn(['social_provider', 'social_id']);
        });
    }
};
