<?php

namespace App\Console\Commands;

use App\Services\QuestionBank\QuestionBankBackupService;
use Illuminate\Console\Command;

/**
 * Daily snapshot of the practice question bank, keeping the last 30 days.
 * Scheduled in routes/console.php; safe to run by hand at any time.
 */
class BackupQuestionBank extends Command
{
    protected $signature = 'questions:backup';

    protected $description = 'Snapshot the practice question bank and prune backups older than 30 days';

    public function handle(QuestionBankBackupService $service): int
    {
        $backup = $service->runDaily();

        $this->info("Backed up {$backup->question_count} questions ({$backup->path}).");

        return self::SUCCESS;
    }
}
