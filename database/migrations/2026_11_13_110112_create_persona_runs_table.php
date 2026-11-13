<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('persona_runs', function (Blueprint $table) {
            $table->id();
            $table->string('persona');     // anxious_parent | child_full_day
            $table->string('status')->default('running'); // running | completed | failed | skipped_budget
            $table->text('summary')->nullable();
            $table->decimal('cost_usd', 8, 4)->default(0);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();

            $table->index(['persona', 'created_at']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('persona_runs');
    }
};
