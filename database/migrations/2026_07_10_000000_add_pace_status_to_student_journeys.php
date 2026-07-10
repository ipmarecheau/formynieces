<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student_journeys', function (Blueprint $table) {
            $table->string('pace_status')->nullable()->after('exam_date');
            $table->unsignedSmallInteger('weeks_behind')->nullable()->after('pace_status');
        });
    }

    public function down(): void
    {
        Schema::table('student_journeys', function (Blueprint $table) {
            $table->dropColumn(['pace_status', 'weeks_behind']);
        });
    }
};
