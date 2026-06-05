<?php

namespace App\Http\Controllers;

use App\Models\StudentProgress;
use App\Models\WeeklyTarget;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        if ($user->isParent()) {
            return $this->parentDashboard($user);
        }

        return $this->studentDashboard($user);
    }

    private function studentDashboard($user): View
    {
        $weeklyTarget = WeeklyTarget::with('module')
            ->where('student_id', $user->id)
            ->where('week_start_date', now()->startOfWeek())
            ->first();

        $progress = StudentProgress::with('module')
            ->where('student_id', $user->id)
            ->orderBy('module_id')
            ->get();

        $masteredCount = $progress->where('status', 'mastered')->count();
        $totalCount = $progress->count();
        $completionPercent = $totalCount > 0
            ? round(($masteredCount / $totalCount) * 100)
            : 0;

        return view('dashboard', compact(
            'user',
            'weeklyTarget',
            'progress',
            'completionPercent'
        ));
    }

    private function parentDashboard($user): View
    {
        $students = $user->students()->with([
            'progress.module',
            'weeklyTargets' => fn($q) => $q->where('week_start_date', now()->startOfWeek())
                                           ->with('module'),
        ])->get();

        $studentSummaries = $students->map(function ($student) {
            $progress = $student->progress;
            $masteredCount = $progress->where('status', 'mastered')->count();
            $totalCount = $progress->count();
            $completionPercent = $totalCount > 0
                ? round(($masteredCount / $totalCount) * 100)
                : 0;

            $currentTarget = $student->weeklyTargets->first();

            return [
                'student'           => $student,
                'completionPercent' => $completionPercent,
                'masteredCount'     => $masteredCount,
                'totalCount'        => $totalCount,
                'currentTarget'     => $currentTarget,
            ];
        });

        return view('dashboard', compact('user', 'studentSummaries'));
    }
}