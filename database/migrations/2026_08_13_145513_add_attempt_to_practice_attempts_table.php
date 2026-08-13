<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Records which try a diary row was (1 = first, 2 = the retry). A "hard miss" — the signal the
     * re-teach triggers on (LL-14/LL-22) — is an attempt-2 row that is still wrong.
     */
    public function up(): void
    {
        Schema::table('practice_attempts', function (Blueprint $table) {
            $table->unsignedTinyInteger('attempt')->default(1)->after('difficulty');
        });
    }

    public function down(): void
    {
        Schema::table('practice_attempts', function (Blueprint $table) {
            $table->dropColumn('attempt');
        });
    }
};
