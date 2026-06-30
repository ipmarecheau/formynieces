<?php

namespace App\Livewire;

use App\Models\SyllabusModule;
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

    public function mount(SyllabusModule $module): void
    {
        $this->moduleId    = $module->id;
        $this->topic       = $module->topic;
        $this->subject     = $module->subject;
        $this->description = $module->description;
        $this->resources   = $module->resources ?? [];
    }

    public function render()
    {
        return view('livewire.lesson-walk');
    }
}