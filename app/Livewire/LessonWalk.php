<?php

namespace App\Livewire;

use App\Models\Lesson;
use App\Models\SyllabusModule;
use App\Services\GuidedTime;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.diagnostic')]
class LessonWalk extends Component
{
    public int $moduleId;

    public string $topic;

    public string $subject;

    public ?string $description = null;

    public array $resources = [];

    /** True once her 2-hour daily guided pool is spent — the lesson locks for the day (AG-06). */
    public bool $guidedLocked = false;

    /** The authored interactive lesson (LE-01), or null until one is authored. */
    public ?string $lessonTitle = null;

    public array $lessonBlocks = [];

    public function mount(SyllabusModule $module): void
    {
        $this->moduleId = $module->id;
        $this->topic = $module->topic;
        $this->subject = $module->subject;
        $this->description = $module->description;
        $this->resources = $module->resources ?? [];
        $this->guidedLocked = app(GuidedTime::class)->isExhausted(auth()->id());

        $lesson = Lesson::where('module_id', $module->id)->where('is_published', true)->first();
        $this->lessonTitle = $lesson?->title;
        $this->lessonBlocks = $lesson?->blocks ?? [];
    }

    public function render()
    {
        return view('livewire.lesson-walk');
    }
}
