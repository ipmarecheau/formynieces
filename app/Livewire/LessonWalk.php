<?php

namespace App\Livewire;

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

    public function mount(SyllabusModule $module): void
    {
        $this->moduleId = $module->id;
        $this->topic = $module->topic;
        $this->subject = $module->subject;
        $this->description = $module->description;
        $this->resources = $module->resources ?? [];
        $this->guidedLocked = app(GuidedTime::class)->isExhausted(auth()->id());
    }

    public function render()
    {
        return view('livewire.lesson-walk');
    }
}
