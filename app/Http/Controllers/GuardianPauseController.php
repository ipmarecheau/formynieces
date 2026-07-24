<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\Pacing\PauseService;
use Illuminate\Http\RedirectResponse;

/**
 * A guardian pauses or resumes her own student from the Parent Portal.
 * Pausing stops weekly target generation and freezes streaks; resuming
 * re-paces from the resume date. [WT-04 / WT-05 / ML-03]
 */
final class GuardianPauseController extends Controller
{
    public function __construct(
        private PauseService $pauses,
    ) {}

    public function pause(User $student): RedirectResponse
    {
        $this->authorizeOwnership($student);
        $this->pauses->pause($student);

        return back();
    }

    public function resume(User $student): RedirectResponse
    {
        $this->authorizeOwnership($student);
        $this->pauses->resume($student);

        return back();
    }

    /**
     * A guardian may only pause or resume her own child.
     */
    private function authorizeOwnership(User $student): void
    {
        abort_unless($student->parent_id === auth()->id(), 403);
    }
}
