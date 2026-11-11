<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('push_deliveries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type');          // essay.feedback, milestone, re-engagement, billing.renewal...
            $table->string('criticality');   // standard | critical
            $table->string('platform')->nullable();
            $table->string('outcome');       // delivered | suppressed | failed | retryable
            $table->string('reason')->nullable();  // why suppressed/failed
            $table->string('detail', 1024)->nullable(); // gateway response
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
            $table->index(['outcome', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('push_deliveries');
    }
};
