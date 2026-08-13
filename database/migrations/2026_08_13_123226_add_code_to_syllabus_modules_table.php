<?php

use App\Models\SyllabusModule;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Subject -> short code prefix. */
    private const PREFIXES = ['Math' => 'MATH', 'ELA' => 'ELA'];

    /**
     * Add a stable, human-readable module code (e.g. MATH-001) — the key lesson imports bind to,
     * so an import survives topic renames and reseeds. Backfilled for every existing module.
     */
    public function up(): void
    {
        Schema::table('syllabus_modules', function (Blueprint $table) {
            $table->string('code')->nullable()->after('id');
        });

        foreach (self::PREFIXES as $subject => $prefix) {
            $modules = SyllabusModule::query()
                ->where('subject', $subject)
                ->orderBy('sequence_order')
                ->orderBy('id')
                ->get();

            $rank = 0;
            foreach ($modules as $module) {
                $rank++;
                $module->code = sprintf('%s-%03d', $prefix, $rank);
                $module->save();
            }
        }

        Schema::table('syllabus_modules', function (Blueprint $table) {
            $table->unique('code');
        });
    }

    public function down(): void
    {
        Schema::table('syllabus_modules', function (Blueprint $table) {
            $table->dropUnique(['code']);
            $table->dropColumn('code');
        });
    }
};
