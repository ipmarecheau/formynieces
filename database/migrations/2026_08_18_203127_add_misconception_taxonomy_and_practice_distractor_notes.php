<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * LL-09 — targeted corrections in the practice loop.
 *
 * A shared misconception taxonomy (module-scoped): each named misconception carries
 * ONE reusable worked example, so a distractor across many questions can point at the
 * same correction (approach B — shared taxonomy, not per-option-unique content).
 *
 * practice_questions.distractor_notes mirrors the diagnostic's AnchorQuestion shape:
 *   {"misconceptions": {"<optionIndex>": "<misconception key>"}}
 * When a chosen wrong option is tagged, the correction names that misconception and
 * shows its worked example; untagged options fall back to the generic explanation
 * (progressive enhancement — the mechanism ships now, content is backfilled later).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('misconceptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('module_id')->constrained('syllabus_modules')->cascadeOnDelete();
            $table->string('key'); // slug, unique within a module
            $table->string('label'); // the specific misconception, named kindly to the child
            $table->text('worked_example'); // one reusable worked example addressing it
            $table->timestamps();
            $table->unique(['module_id', 'key']);
        });

        Schema::table('practice_questions', function (Blueprint $table) {
            $table->json('distractor_notes')->nullable()->after('explanation');
        });
    }

    public function down(): void
    {
        Schema::table('practice_questions', function (Blueprint $table) {
            $table->dropColumn('distractor_notes');
        });
        Schema::dropIfExists('misconceptions');
    }
};
