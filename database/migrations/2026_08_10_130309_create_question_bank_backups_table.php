<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A catalogue of practice-question-bank snapshots. Each row records one backup —
 * the file holding the captured questions, how many there were, and why it was
 * taken (daily / before delete-all / before restore). Restores and pruning read
 * from here.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('question_bank_backups', function (Blueprint $table) {
            $table->id();
            $table->string('reason')->default('manual'); // daily | before-delete-all | before-restore | manual
            $table->unsignedInteger('question_count')->default(0);
            $table->string('path'); // relative path on the 'local' disk to the JSON snapshot
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('question_bank_backups');
    }
};
