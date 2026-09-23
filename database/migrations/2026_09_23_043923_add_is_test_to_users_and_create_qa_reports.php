<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Marks synthetic QA / agent accounts so their activity can be excluded
            // from real analytics and cleanly removed. Never set for real families.
            $table->boolean('is_test')->default(false)->index();
        });

        // The QA report inbox: an autonomous agent POSTs a structured finding per
        // scenario each run; the dashboard reads these back newest-first.
        Schema::create('qa_reports', function (Blueprint $table) {
            $table->id();
            $table->string('scenario')->nullable()->index();   // user-story id, e.g. LL-20
            $table->string('outcome')->default('note')->index(); // pass | fail | blocked | gap | note
            $table->string('summary');
            $table->text('detail')->nullable();                 // steps taken, gap found, etc.
            $table->string('actor')->nullable();                // which agent / account
            $table->string('screenshot_url')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('qa_reports');
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('is_test');
        });
    }
};
