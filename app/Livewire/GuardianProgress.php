<?php

namespace App\Livewire;

use App\Models\User;
use App\Models\StudentProgress;
use App\Models\SyllabusModule;
use Livewire\Component;
use Livewire\Attributes\Layout;

class GuardianProgress extends Component
{
    /** Subjects that group real modules into buckets. Writing is a parallel track (WR-01–05). */
    private const MODULE_SUBJECTS = ['Math', 'ELA'];

    /** student_progress.status → drill-down bucket key. */
    private const STATUS_BUCKET = [
        'mastered'          => 'mastered',
        'inferred_mastered' => 'in_review',
        'needs_work'        => 'working_on',
        'not_started'       => 'upcoming',
    ];

    #[Layout('layouts.guardian')]
    public function render()
    {
        $guardian = auth()->user();
        $student  = $guardian->students()->first();

        return view('livewire.guardian-progress', [
            'buckets' => $this->buildBuckets($student),
        ]);
    }

    /**
     * @return array<string, array<string, array<int, array{id:int, topic:string}>>>
     *         subject => bucket => list of {id, topic}
     */
    private function buildBuckets(?User $student): array
    {
        $progressByModule = $student
            ? StudentProgress::where('student_id', $student->id)->get()->keyBy('module_id')
            : collect();

        $buckets = [];

        foreach (self::MODULE_SUBJECTS as $subject) {
            $buckets[$subject] = [
                'mastered'   => [],
                'in_review'  => [],
                'working_on' => [],
                'upcoming'   => [],
            ];

            $modules = SyllabusModule::where('subject', $subject)
                ->orderBy('pacing_week')
                ->orderBy('sequence_order')
                ->get();

            foreach ($modules as $module) {
                $status = $progressByModule->get($module->id)?->status ?? 'not_started';
                $bucket = self::STATUS_BUCKET[$status] ?? 'upcoming';

                $buckets[$subject][$bucket][] = [
                    'id'    => $module->id,
                    'topic' => $module->topic,
                ];
            }
        }

        return $buckets;
    }
}
