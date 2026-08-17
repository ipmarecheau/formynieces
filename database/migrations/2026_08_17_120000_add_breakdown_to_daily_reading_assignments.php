<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Persist the day's word sentences and a comprehension feedback summary so the
     * done screen can show an honest breakdown and the learning diary can record it.
     */
    public function up(): void
    {
        Schema::table('daily_reading_assignments', function (Blueprint $table) {
            $table->json('vocab_sentences')->nullable()->after('answers'); // {word_id: sentence}
            $table->text('comprehension_feedback')->nullable()->after('comprehension_score');
        });
    }

    public function down(): void
    {
        Schema::table('daily_reading_assignments', function (Blueprint $table) {
            $table->dropColumn(['vocab_sentences', 'comprehension_feedback']);
        });
    }
};
