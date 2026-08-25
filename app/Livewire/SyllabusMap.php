<?php

namespace App\Livewire;

use App\Models\Lesson;
use App\Models\PracticeQuestion;
use App\Models\StudentProgress;
use App\Models\SyllabusModule;
use App\Models\User;
use App\Services\Pacing\AdventureMapBuilder;
use Illuminate\Support\Str;
use Livewire\Component;

/**
 * SyllabusMap — a read-only coverage view: every SEA objective (module) grouped by subject and
 * strand, showing which have a lesson, how many practice questions back them, the child's progress,
 * and the cognitive emphasis (K/A/R). Students see their own; guardians see their child's. Lessons
 * are opened from the Voyage, never launched here (lesson-development standard §6).
 */
class SyllabusMap extends Component
{
    /** status → [label, percent] for the progress bar. */
    private const STATUS = [
        'mastered' => ['Mastered', 100],
        'inferred_mastered' => ['In review', 75],
        'needs_work' => ['Working on', 40],
        'not_started' => ['Not started', 0],
    ];

    /** Topic/strand cues that add a Reasoning emphasis (multi-step, "why", evaluation, writing). */
    private const REASONING_CUES = [
        'Multi-step', 'Problem Solving', 'Problems', 'Patterns', 'Algebraic', 'Proportion',
        'Analysing', 'Draw Conclusions', 'Inferring', 'Purpose', 'Evaluating', 'Point of View',
        'Judgements', 'Solutions', 'Appreciation', 'Writing', 'Implied', 'Cause and Effect',
    ];

    private function resolveStudent(): ?User
    {
        $user = auth()->user();
        if ($user === null) {
            return null;
        }

        return $user->isStudent() ? $user : $user->students()->first();
    }

    /** @return array<int, string> the cognitive dimensions to show for a module. */
    private function karFor(SyllabusModule $m): array
    {
        $kar = ['K', 'A'];
        foreach (self::REASONING_CUES as $cue) {
            if (Str::contains($m->topic, $cue)) {
                $kar[] = 'R';
                break;
            }
        }

        return $kar;
    }

    public function render()
    {
        $student = $this->resolveStudent();
        $isGuardian = auth()->user()?->isStudent() === false;

        $lessonModuleIds = Lesson::where('is_published', true)->pluck('module_id')->flip();
        $questionCounts = PracticeQuestion::selectRaw('module_id, count(*) as c')
            ->groupBy('module_id')->pluck('c', 'module_id');
        $progress = $student
            ? StudentProgress::where('student_id', $student->id)->get()->keyBy('module_id')
            : collect();

        $map = app(AdventureMapBuilder::class);

        $rows = SyllabusModule::orderBy('subject')->orderBy('sequence_order')->get()
            ->map(function (SyllabusModule $m) use ($lessonModuleIds, $questionCounts, $progress, $student, $isGuardian, $map): array {
                $status = $progress->get($m->id)?->status ?? 'not_started';
                [$label, $pct] = self::STATUS[$status] ?? self::STATUS['not_started'];
                $strand = trim(Str::contains($m->topic, ':') ? Str::before($m->topic, ':') : $m->subject);
                $short = trim(Str::contains($m->topic, ':') ? Str::after($m->topic, ':') : $m->topic);

                // Deep-link to the ISLAND (the Voyage), never straight into the lesson — and only
                // for the student's own view.
                $voyageUrl = null;
                if (! $isGuardian && $student) {
                    $slug = $map->islandSlugForModule($student, $m->id);
                    $voyageUrl = $slug ? route('student.voyage.island', $slug) : route('student.voyage');
                }

                return [
                    'code' => $m->code,
                    'subject' => $m->subject,
                    'strand' => $strand,
                    'topic' => $short,
                    'has_lesson' => $lessonModuleIds->has($m->id),
                    'questions' => (int) ($questionCounts[$m->id] ?? 0),
                    'kar' => $this->karFor($m),
                    'status_label' => $label,
                    'progress' => $pct,
                    'voyage_url' => $voyageUrl,
                ];
            });

        $grouped = $rows->groupBy('subject')->map(fn ($subjectRows) => $subjectRows->groupBy('strand'));

        $view = view('livewire.syllabus-map', [
            'grouped' => $grouped,
            'student' => $student,
            'isGuardian' => $isGuardian,
            'lessonCount' => $rows->where('has_lesson', true)->count(),
            'total' => $rows->count(),
        ]);

        return $isGuardian ? $view->layout('layouts.guardian') : $view->layout('components.layouts.diagnostic');
    }
}
