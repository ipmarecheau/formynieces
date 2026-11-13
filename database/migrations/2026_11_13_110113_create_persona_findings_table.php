<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('persona_findings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('persona_run_id')->constrained()->cascadeOnDelete();
            $table->string('severity')->default('medium'); // low | medium | high
            $table->string('area');                         // onboarding, voyage, writing, billing...
            $table->text('observation');                    // what the persona hit
            $table->text('suggestion')->nullable();         // what to do about it
            $table->string('admin_verdict')->nullable();    // valid | invalid | wont_fix | fixed
            $table->text('admin_notes')->nullable();
            $table->timestamps();

            $table->index(['severity', 'admin_verdict']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('persona_findings');
    }
};
