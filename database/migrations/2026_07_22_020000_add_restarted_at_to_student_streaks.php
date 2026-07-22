<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student_streaks', function (Blueprint $table) {
            $table->date('restarted_at')->nullable()->after('last_activity_date');
        });
    }

    public function down(): void
    {
        Schema::table('student_streaks', function (Blueprint $table) {
            $table->dropColumn('restarted_at');
        });
    }
};
