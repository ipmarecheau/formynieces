<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('syllabus_modules', function (Blueprint $table) {
            $table->unsignedTinyInteger('pacing_week')->default(1)->after('sequence_order');
        });
    }

    public function down(): void
    {
        Schema::table('syllabus_modules', function (Blueprint $table) {
            $table->dropColumn('pacing_week');
        });
    }
};