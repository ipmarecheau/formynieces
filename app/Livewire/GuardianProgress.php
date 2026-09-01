<?php

namespace App\Livewire;

use App\Models\StudentProgress;
use App\Models\SyllabusModule;
use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Component;

class GuardianProgress extends Component
{
    /** Subjects that group real modules into buckets. Writing is a parallel track (WR-01–05). */
    private const MODULE_SUBJECTS = ['Math', 'ELA'];

    /** student_progress.status → drill-down bucket key. */
    private const STATUS_BUCKET = [
        'mastered' => 'mastered',
        'inferred_mastered' => 'in_review',
        'needs_work' => 'working_on',
        'not_started' => 'upcoming',
    ];

    #[Layout('layouts.guardian')]
    public function render()
    {
        $guardian = auth()->user();
        $student = $guardian->students()->first();

        $buckets = $this->buildBuckets($student);

        return view('livewire.guardian-progress', [
            'buckets' => $buckets,
            'summaries' => $this->summarise($buckets),
            'hasChild' => $student !== null,
        ]);
    }

    /**
     * Per-subject mastered-out-of-total summary for the drill-down header.
     *
     * @param  array<string, array<string, array<int, mixed>>>  $buckets
     * @return array<string, array{mastered:int, total:int}>
     */
    private function summarise(array $buckets): array
    {
        $summaries = [];

        foreach ($buckets as $subject => $subjectBuckets) {
            $summaries[$subject] = [
                'mastered' => count($subjectBuckets['mastered']),
                'total' => array_sum(array_map('count', $subjectBuckets)),
            ];
        }

        return $summaries;
    }

    /**
     * @return array<string, array<string, array<int, array{id:int, topic:string}>>>
     *                                                                               subject => bucket => list of {id, topic}
     */
    private function buildBuckets(?User $student): array
    {
        $progressByModule = $student
            ? StudentProgress::where('student_id', $student->id)->get()->keyBy('module_id')
            : collect();

        $buckets = [];

        foreach (self::MODULE_SUBJECTS as $subject) {
            $buckets[$subject] = [
                'mastered' => [],
                'in_review' => [],
                'working_on' => [],
                'upcoming' => [],
            ];

            $modules = SyllabusModule::where('subject', $subject)
                ->orderBy('pacing_week')
                ->orderBy('sequence_order')
                ->get();

            foreach ($modules as $module) {
                $status = $progressByModule->get($module->id)?->status ?? 'not_started';
                $bucket = self::STATUS_BUCKET[$status] ?? 'upcoming';

                $buckets[$subject][$bucket][] = [
                    'id' => $module->id,
                    'topic' => $module->topic,
                ];
            }
        }

        return $buckets;
    }
}
