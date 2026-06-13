<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Slice 1 — Migration 1 of 6: Subject remap.
 *
 * The syllabus_modules.subject column carries a SQLite CHECK constraint baked
 * into the table definition:
 *   check ("subject" in ('Math', 'English Editing', 'English Comprehension'))
 * so a plain UPDATE to 'ELA' violates the constraint. SQLite can't ALTER a
 * check in place, so we first rebuild the column with a widened allowed set
 * (enum()->change() triggers a full table rebuild), THEN remap the data.
 *
 * Final allowed set: ['Math', 'ELA'].
 *
 * Section discriminator is unchanged: ELA Section I = Language
 * (was English Editing), Section II = Comprehension, Section III = Editing-type.
 */
return new class extends Migration
{
    public function up(): void
    {
        // 1. Widen the check to include both old and new values so the UPDATE
        //    is legal during the transition.
        Schema::table('syllabus_modules', function (Blueprint $table) {
            $table->enum('subject', [
                'Math',
                'English Editing',
                'English Comprehension',
                'ELA',
            ])->change();
        });

        // 2. Remap the data.
        DB::table('syllabus_modules')
            ->whereIn('subject', ['English Editing', 'English Comprehension'])
            ->update(['subject' => 'ELA']);

        // 3. Narrow the check to the final allowed set.
        Schema::table('syllabus_modules', function (Blueprint $table) {
            $table->enum('subject', ['Math', 'ELA'])->change();
        });
    }

    public function down(): void
    {
        // 1. Widen back so the old names are legal again.
        Schema::table('syllabus_modules', function (Blueprint $table) {
            $table->enum('subject', [
                'Math',
                'English Editing',
                'English Comprehension',
                'ELA',
            ])->change();
        });

        // 2. Restore original names by section (lossless for current data).
        DB::table('syllabus_modules')
            ->where('subject', 'ELA')
            ->where('sea_section', 'Section II')
            ->update(['subject' => 'English Comprehension']);

        DB::table('syllabus_modules')
            ->where('subject', 'ELA')
            ->whereIn('sea_section', ['Section I', 'Section III'])
            ->update(['subject' => 'English Editing']);

        // 3. Restore the original narrow check.
        Schema::table('syllabus_modules', function (Blueprint $table) {
            $table->enum('subject', [
                'Math',
                'English Editing',
                'English Comprehension',
            ])->change();
        });
    }
};
