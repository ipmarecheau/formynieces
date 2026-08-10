<?php

declare(strict_types=1);

namespace App\Services\QuestionBank;

use App\Models\QuestionBankBackup;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Backup, restore, and pruning for the practice question bank.
 *
 * Each backup is a JSON snapshot of every `practice_questions` row (raw columns,
 * ids included, so a restore is exact) written to the 'local' disk, catalogued by
 * a QuestionBankBackup row. Destructive operations (delete-all, restore) always
 * take a safety backup first. A daily run keeps the last 30 days and prunes older.
 */
class QuestionBankBackupService
{
    private string $directory = 'backups/question-bank';

    private int $retentionDays = 30;

    /**
     * Snapshot the whole bank to a dated JSON file and catalogue it.
     */
    public function backup(string $reason = 'manual'): QuestionBankBackup
    {
        $rows = DB::table('practice_questions')->orderBy('id')->get();
        $path = $this->directory.'/'.now()->format('Y-m-d_His_u').'.json';

        Storage::disk('local')->put($path, $rows->toJson());

        return QuestionBankBackup::create([
            'reason' => $reason,
            'question_count' => $rows->count(),
            'path' => $path,
        ]);
    }

    /**
     * The daily job: take a dated backup, then prune anything older than 30 days.
     */
    public function runDaily(): QuestionBankBackup
    {
        $backup = $this->backup('daily');
        $this->prune();

        return $backup;
    }

    /**
     * Back the bank up, then empty it. Returns the safety backup.
     */
    public function deleteAll(): QuestionBankBackup
    {
        $backup = $this->backup('before-delete-all');
        DB::table('practice_questions')->delete();

        return $backup;
    }

    /**
     * Restore the bank to a catalogued snapshot — taking a safety backup of the
     * current state first, then replacing every question with the snapshot's.
     */
    public function restore(QuestionBankBackup $backup): void
    {
        $this->backup('before-restore');

        $rows = json_decode((string) Storage::disk('local')->get($backup->path), true) ?: [];

        DB::transaction(function () use ($rows) {
            // The snapshot restores questions by their original ids, so any
            // practice attempts pointing at them stay valid — defer FK checks to
            // commit so the intermediate delete/insert swap does not trip them.
            if (DB::connection()->getDriverName() === 'sqlite') {
                DB::statement('PRAGMA defer_foreign_keys = ON');
            }

            DB::table('practice_questions')->delete();
            foreach (array_chunk($rows, 200) as $chunk) {
                DB::table('practice_questions')->insert(array_map(fn ($row): array => (array) $row, $chunk));
            }
        });
    }

    /**
     * Remove catalogued backups (and their files) older than the retention window.
     *
     * @return int how many were removed
     */
    public function prune(): int
    {
        $cutoff = now()->subDays($this->retentionDays);
        $old = QuestionBankBackup::where('created_at', '<', $cutoff)->get();

        foreach ($old as $backup) {
            Storage::disk('local')->delete($backup->path);
            $backup->delete();
        }

        return $old->count();
    }
}
