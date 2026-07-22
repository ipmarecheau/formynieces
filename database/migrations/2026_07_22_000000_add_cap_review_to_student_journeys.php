<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student_journeys', function (Blueprint $table) {
            $table->boolean('cap_review_required')->default(false)->after('weeks_behind');
            $table->unsignedSmallInteger('required_pace')->nullable()->after('cap_review_required');
        });
    }

    public function down(): void
    {
        Schema::table('student_journeys', function (Blueprint $table) {
            $table->dropColumn(['cap_review_required', 'required_pace']);
        });
    }
};
