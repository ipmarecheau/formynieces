<?php

namespace App\Services\Pacing;

use App\Models\StudentProgress;
use App\Models\SyllabusModule;
use App\Models\User;
use App\Models\WeeklyTarget;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class WeeklyRollover
{
    public function __construct(
        private readonly CapResolver $capResolver,
    ) {
    }

    public function runFor(User $student, ?Carbon $now = null): Collection
{
    $now = $now ?? Carbon::today();
    $weekStart = $now->copy()->startOfWeek()->toDateString();
    $cap = $this->capResolver->resolve($student);

    $existingThisWeek = WeeklyTarget::where('student_id', $student->id)
        ->where('week_start_date', $weekStart)
        ->pluck('module_id');

    $remainingCap = max(0, $cap - $existingThisWeek->count());

    $masteredModuleIds = StudentProgress::where('student_id', $student->id)
        ->where('status', 'mastered')
        ->pluck('module_id');

    $excludeIds = $masteredModuleIds->merge($existingThisWeek);

    $backlogModuleIds = WeeklyTarget::where('student_id', $student->id)
        ->where('week_start_date', '<', $weekStart)
        ->whereNotIn('module_id', $excludeIds)
        ->orderBy('week_start_date')
        ->pluck('module_id')
        ->unique()
        ->values();

    $selectedIds = $backlogModuleIds->take($remainingCap);
    $remaining = $remainingCap - $selectedIds->count();

    if ($remaining > 0) {
        $frontierIds = SyllabusModule::whereNotIn('id', $excludeIds)
            ->whereNotIn('id', $selectedIds)
            ->orderBy('pacing_week')
            ->orderBy('sequence_order')
            ->limit($remaining)
            ->pluck('id');

        $selectedIds = $selectedIds->concat($frontierIds);
    }

    foreach ($selectedIds as $moduleId) {
        WeeklyTarget::updateOrCreate(
            [
                'student_id' => $student->id,
                'module_id' => $moduleId,
                'week_start_date' => $weekStart,
            ],
            ['is_completed' => false],
        );
    }

    return WeeklyTarget::where('student_id', $student->id)
        ->where('week_start_date', $weekStart)
        ->get();
    }
}
