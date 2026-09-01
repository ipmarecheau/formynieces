<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * A recoverable (encrypted, not hashed) copy of a child's generated password,
     * so the managing guardian can reveal or reset it from the Parent Portal.
     * Only ever set for student accounts created by a guardian.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->text('child_password_enc')->nullable()->after('password');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('child_password_enc');
        });
    }
};
