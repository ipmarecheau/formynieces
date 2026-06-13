<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Slice 1 — Migration 2 of 6: Module prerequisite graph.
 *
 * A self-referencing many-to-many edge list over syllabus_modules. Used by the
 * diagnostic to propagate inferred mastery down a prerequisite chain when an
 * anchor is answered correctly, and to block propagation when a harder anchor
 * in the same chain is missed (diagnostic.feature).
 *
 *   module_id              -> the module that HAS a prerequisite
 *   prerequisite_module_id -> the module that must come first
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('module_prerequisites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('module_id')
                ->constrained('syllabus_modules')
                ->cascadeOnDelete();
            $table->foreignId('prerequisite_module_id')
                ->constrained('syllabus_modules')
                ->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['module_id', 'prerequisite_module_id'], 'module_prereq_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('module_prerequisites');
    }
};
