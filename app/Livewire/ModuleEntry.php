<?php

namespace App\Livewire;

use App\Models\SyllabusModule;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * ModuleEntry — the front door to a module's learning loop.
 *
 * Opening a level lands here first. It runs three phases:
 *  - explainer: a short, student-language account of how the loop works (LL-19),
 *    with a button that leads into the competency check.
 *  - check: the fast D1/D3/D5 test-out (LL-20).
 *  - outcome: mastered celebration, or a choice of lesson / tutorial / practice (LL-21).
 *
 * This loop only builds the explainer phase and the transition into the check;
 * the check and outcome phases are filled by LL-20 and LL-21.
 */
#[Layout('components.layouts.diagnostic')]
class ModuleEntry extends Component
{
    public int $moduleId;

    public string $topic;

    /** explainer | check | outcome */
    public string $phase = 'explainer';

    public function mount(SyllabusModule $module): void
    {
        $this->moduleId = $module->id;
        $this->topic = $module->topic;
    }

    /** Leave the explainer and begin the competency check. */
    public function beginCheck(): void
    {
        $this->phase = 'check';
    }

    public function render()
    {
        return view('livewire.module-entry');
    }
}
