<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\Diagnostic\ReconciliationResolver;
use Illuminate\Http\RedirectResponse;

/**
 * Handles a guardian's reconciliation decision from the Parent Portal: either
 * proceed with the diagnostic result or keep her stated weak areas. [RR-04]
 */
final class GuardianReconciliationController extends Controller
{
    public function __construct(
        private ReconciliationResolver $resolver,
    ) {}

    public function proceed(User $student): RedirectResponse
    {
        $this->authorizeOwnership($student);
        $this->resolver->proceedWithDiagnostic($student);

        return back();
    }

    public function keep(User $student): RedirectResponse
    {
        $this->authorizeOwnership($student);
        $this->resolver->keepStatedWeakAreas($student);

        return back();
    }

    /**
     * A guardian may only reconcile her own child.
     */
    private function authorizeOwnership(User $student): void
    {
        abort_unless($student->parent_id === auth()->id(), 403);
    }
}
