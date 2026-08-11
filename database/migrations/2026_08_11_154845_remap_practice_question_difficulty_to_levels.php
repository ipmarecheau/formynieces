<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * The learning-loop climb now uses the question bank's real difficulty levels
 * D1 -> D3 -> D5 as its three rungs, instead of the collapsed 1/2/3. Remap the
 * existing practice bank onto those values: rung 2 (medium) -> 3, rung 3 (hard) -> 5;
 * rung 1 (easy) is unchanged. Order matters — lift the hard rung first so the
 * medium remap can't collide with it.
 *
 * Only `practice_questions` is remapped. The writing bank keeps its own 1/2/3 rung
 * scale (it is not part of the mastery climb).
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('practice_questions')->where('difficulty', 3)->update(['difficulty' => 5]);
        DB::table('practice_questions')->where('difficulty', 2)->update(['difficulty' => 3]);
        DB::table('practice_attempts')->where('difficulty', 3)->update(['difficulty' => 5]);
        DB::table('practice_attempts')->where('difficulty', 2)->update(['difficulty' => 3]);
    }

    public function down(): void
    {
        DB::table('practice_questions')->where('difficulty', 5)->update(['difficulty' => 3]);
        DB::table('practice_questions')->where('difficulty', 3)->update(['difficulty' => 2]);
        DB::table('practice_attempts')->where('difficulty', 5)->update(['difficulty' => 3]);
        DB::table('practice_attempts')->where('difficulty', 3)->update(['difficulty' => 2]);
    }
};
