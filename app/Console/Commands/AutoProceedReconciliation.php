<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\User;
use App\Services\Diagnostic\DiagnosticReconciliation;
use App\Services\Diagnostic\ReconciliationResolver;
use Illuminate\Console\Command;

final class AutoProceedReconciliation extends Command
{
    protected $signature = 'reconciliation:auto-proceed';

    protected $description = 'Auto-proceed guardian reconciliations left unanswered for three or more days (RR-10).';

    public function handle(DiagnosticReconciliation $reconciliation, ReconciliationResolver $resolver): int
    {
        $cutoff = now()->subDays(3);

        $candidates = User::where('role', 'student')
            ->whereNull('onboarding_completed_at')
            ->whereNull('guardian_reconciled_at')
            ->whereHas('diagnosticSessions', function ($query) use ($cutoff): void {
                $query->where('status', 'completed')
                    ->where('completed_at', '<=', $cutoff);
            })
            ->get();

        $count = 0;

        foreach ($candidates as $student) {
            if ($reconciliation->requiresGuardianDecision($student)) {
                $resolver->proceedWithDiagnostic($student);
                $count++;
            }
        }

        $this->info("Auto-proceeded {$count} reconciliation(s).");

        return self::SUCCESS;
    }
}
