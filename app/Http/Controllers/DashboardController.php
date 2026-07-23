<?php

namespace App\Http\Controllers;

use App\Models\StudentProgress;
use App\Models\StudentStreak;
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

    /**
     * Celebrate a returning student's active streaks before she continues to her map.
     */
    public function studentSplash(Request $request): View
    {
        $user = $request->user();

        $practiceStreak = StudentStreak::where('student_id', $user->id)
            ->where('type', 'practice')
            ->value('count') ?? 0;
        $loginStreak = StudentStreak::where('student_id', $user->id)
            ->where('type', 'login')
            ->value('count') ?? 0;
        $masteryStreak = StudentStreak::where('student_id', $user->id)
            ->where('type', 'mastery')
            ->value('count') ?? 0;
        $paceStreak = StudentStreak::where('student_id', $user->id)
            ->where('type', 'pace_weeks')
            ->value('count') ?? 0;

        return view('student-splash', compact(
            'user',
            'practiceStreak',
            'loginStreak',
            'masteryStreak',
            'paceStreak',
        ));
    }

    private function studentDashboard($user): View
    {
        $weeklyTarget = WeeklyTarget::with('module')
            ->where('student_id', $user->id)
            ->where('week_start_date', now()->startOfWeek()->toDateString())
            ->first();

        $progress = StudentProgress::with('module')
            ->where('student_id', $user->id)
            ->get();

        $masteredCount = $progress->where('status', 'mastered')->count();
        $likelyCount = $progress->where('status', 'inferred_mastered')->count();
        $needsCount = $progress->where('status', 'needs_work')->count();
        $totalCount = $progress->count();
        $completionPercent = $totalCount > 0
            ? round(($masteredCount / $totalCount) * 100)
            : 0;

        // Build Subject → topic-prefix → modules hierarchy with per-group tallies.
        $roadmap = $this->buildRoadmap($progress);

        // Practice day-streak (0 if the student has no practice activity yet).
        $dayStreak = StudentStreak::where('student_id', $user->id)
            ->where('type', 'practice')
            ->value('count') ?? 0;

        // Login day-streak (0 if the student has never signed in before).
        $loginStreak = StudentStreak::where('student_id', $user->id)
            ->where('type', 'login')
            ->value('count') ?? 0;

        // Mastery day-streak (0 if the student has not mastered a module yet).
        $masteryStreak = StudentStreak::where('student_id', $user->id)
            ->where('type', 'mastery')
            ->value('count') ?? 0;

        // On-pace week-streak (0 if the student has not had an on-pace week yet).
        // A kind, student-facing celebration of staying on track across weeks.
        $paceStreak = StudentStreak::where('student_id', $user->id)
            ->where('type', 'pace_weeks')
            ->value('count') ?? 0;

        // A kind "welcome back" shows only on the calendar day a broken practice
        // streak was restarted (recordActivity marked restarted_at = today).
        $streakRestarted = StudentStreak::where('student_id', $user->id)
            ->where('type', 'practice')
            ->whereDate('restarted_at', today())
            ->exists();

        return view('dashboard', compact(
            'user',
            'weeklyTarget',
            'progress',
            'roadmap',
            'masteredCount',
            'likelyCount',
            'needsCount',
            'completionPercent',
            'dayStreak',
            'loginStreak',
            'masteryStreak',
            'paceStreak',
            'streakRestarted'
        ));
    }

    /**
     * Group progress rows into Subject => [ prefix => ['items' => [...], 'tally' => [...]] ].
     * Prefix is the part of the topic before the first colon; the leaf is the rest.
     */
    private function buildRoadmap($progress): array
    {
        $roadmap = [];

        foreach ($progress as $item) {
            $subject = $item->module->subject;
            $topic = $item->module->topic;

            [$prefix, $leaf] = $this->splitTopic($topic);

            $roadmap[$subject] ??= [];
            $roadmap[$subject][$prefix] ??= [
                'items' => [],
                'tally' => ['mastered' => 0, 'inferred_mastered' => 0, 'needs_work' => 0],
            ];

            $roadmap[$subject][$prefix]['items'][] = [
                'id' => $item->module->id,
                'leaf' => $leaf,
                'status' => $item->status,
                'section' => $item->module->sea_section,
            ];

            if (isset($roadmap[$subject][$prefix]['tally'][$item->status])) {
                $roadmap[$subject][$prefix]['tally'][$item->status]++;
            }
        }

        return $roadmap;
    }

    /** "Fractions: Addition and Subtraction" => ['Fractions', 'Addition and Subtraction']. */
    private function splitTopic(string $topic): array
    {
        $pos = strpos($topic, ':');
        if ($pos === false) {
            return [$topic, $topic]; // no colon — prefix and leaf are the same
        }

        return [
            trim(substr($topic, 0, $pos)),
            trim(substr($topic, $pos + 1)),
        ];
    }

    private function parentDashboard($user): View
    {
        $students = $user->students()->with([
            'progress.module',
            'weeklyTargets' => fn ($q) => $q->where('week_start_date', now()->startOfWeek()->toDateString())
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
                'student' => $student,
                'completionPercent' => $completionPercent,
                'masteredCount' => $masteredCount,
                'totalCount' => $totalCount,
                'currentTarget' => $currentTarget,
            ];
        });

        return view('dashboard', compact('user', 'studentSummaries'));
    }
}
