<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * One-off cleanup: the `device_tokens` table was created on prod when the native-app
 * notification migration briefly ran during an accidental deploy. That work has been
 * reverted off `main` and now lives on the `capacitor-shell` branch.
 *
 * This drops the orphan table AND removes the original create-migration's ledger row, so
 * that when `capacitor-shell` is later merged into `main`, its create migration re-runs
 * and recreates the table (otherwise the ledger would mark it "already run" and skip it).
 *
 * Idempotent and safe on a fresh database (both steps become no-ops).
 */
return new class extends Migration
{
    private const CREATE_MIGRATION = '2026_10_04_041432_create_device_tokens_table';

    public function up(): void
    {
        Schema::dropIfExists('device_tokens');

        DB::table('migrations')->where('migration', self::CREATE_MIGRATION)->delete();
    }

    public function down(): void
    {
        // Cleanup migration — nothing to roll back. The table is re-created by the
        // original create migration (on capacitor-shell) when that branch is merged.
    }
};
