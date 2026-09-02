<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Leads captured by the placement-report funnel (lead_capture.feature). A lead is a
 * parent who gave their email (and optionally a WhatsApp number) for the free SEA mock
 * and first-choice placement report, before ever holding an account.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->string('email')->index();
            $table->string('whatsapp')->nullable();
            $table->string('child_name')->nullable();
            $table->string('child_level')->nullable();   // e.g. "Standard 4"
            $table->string('source')->nullable();        // where the lead came from
            $table->foreignId('mock_session_id')->nullable();
            $table->unsignedTinyInteger('mock_score')->nullable();      // 0..100
            $table->string('placement_band')->nullable();               // projected first-choice readiness band
            $table->json('weakest_strands')->nullable();                // segmentation tags
            $table->string('next_step')->nullable();                    // the single recommended next step
            $table->boolean('weekly_opt_in')->default(false);           // SEA Question of the Week nurture
            $table->foreignId('converted_user_id')->nullable();         // set when the lead claims the trial
            $table->timestamp('converted_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};
