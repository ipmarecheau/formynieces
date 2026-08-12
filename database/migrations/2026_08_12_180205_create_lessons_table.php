<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The interactive lesson authored in advance for a module (LE-01). One per module.
 * `blocks` holds the ordered content (text/media/interactive) — a placeholder shape now;
 * the H5P-grade authoring engine (LE-05) fills it out. Never generated in real time.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lessons', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('module_id')->unique()->constrained('syllabus_modules')->cascadeOnDelete();
            $table->string('title');
            $table->json('blocks')->nullable();
            $table->boolean('is_published')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lessons');
    }
};
