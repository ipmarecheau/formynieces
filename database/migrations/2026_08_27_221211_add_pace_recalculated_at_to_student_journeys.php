<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student_journeys', function (Blueprint $table): void {
            // When the weekly pace/progress recalculation last ran for this
            // student — surfaced on the guardian dashboard as "Progress updated".
            $table->timestamp('pace_recalculated_at')->nullable()->after('required_pace');
        });
    }

    public function down(): void
    {
        Schema::table('student_journeys', function (Blueprint $table): void {
            $table->dropColumn('pace_recalculated_at');
        });
    }
};
