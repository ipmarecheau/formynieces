<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * OC-01..05 — parent onboarding calls booked on the public site.
     * call_date + call_time are Trinidad & Tobago local (AST); one call per slot.
     */
    public function up(): void
    {
        Schema::create('onboarding_calls', function (Blueprint $table) {
            $table->id();
            $table->string('parent_name');
            $table->string('email');
            $table->string('phone')->nullable();
            $table->string('child_standard')->nullable();
            $table->text('notes')->nullable();
            $table->date('call_date');
            $table->time('call_time');
            $table->string('status')->default('requested');
            $table->timestamps();
            $table->unique(['call_date', 'call_time']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('onboarding_calls');
    }
};
