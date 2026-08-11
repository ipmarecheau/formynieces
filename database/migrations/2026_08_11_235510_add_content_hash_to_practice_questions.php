<?php

use App\Models\PracticeQuestion;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A stable content hash on every practice question — the key the per-student
 * exposure ledger uses to guarantee a question is never repeated across the loop
 * (diagnostic, tutorial, practice, check). Indexed for fast "not yet seen" filters.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('practice_questions', function (Blueprint $table) {
            $table->string('content_hash')->nullable()->after('source_ref')->index();
        });

        PracticeQuestion::query()
            ->select(['id', 'prompt', 'options'])
            ->chunkById(500, function ($questions) {
                foreach ($questions as $q) {
                    $q->content_hash = PracticeQuestion::hashFor((string) $q->prompt, $q->options ?? []);
                    $q->saveQuietly();
                }
            });
    }

    public function down(): void
    {
        Schema::table('practice_questions', function (Blueprint $table) {
            $table->dropColumn('content_hash');
        });
    }
};
