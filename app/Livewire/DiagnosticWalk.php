<?php
// app/Livewire/DiagnosticWalk.php

namespace App\Livewire;

use App\Services\Diagnostic\ItemWalk;
use App\Services\Diagnostic\SessionPlanner;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class DiagnosticWalk extends Component
{
    public int $sessionId;

    public ?array $question = null;
    public string $prompt = '';
    public array $options = [];

    public function mount(int $sessionId): void
    {
        $this->sessionId = $sessionId;
        $this->loadCurrent();
    }

    protected function walk(): ItemWalk
    {
        return new ItemWalk(new SessionPlanner);
    }

    protected function loadCurrent(): void
    {
        $this->question = $this->walk()->currentQuestion($this->sessionId);

        if ($this->question === null) {
            $this->prompt = '';
            $this->options = [];
            return;
        }

        $anchor = DB::table('anchor_questions')->find($this->question['anchor_id']);
        $this->prompt = $anchor->prompt;
        $this->options = json_decode($anchor->options, true);
    }

    public function choose(int $index): void
    {
        if ($this->question === null) {
            return;
        }

        // Engine records + adapts. We deliberately ignore the returned
        // is_correct / misconception — the child never sees them.
        $this->walk()->submitAnswer(
            $this->sessionId,
            $this->question['anchor_id'],
            $index,
        );

        $this->loadCurrent();
    }

    public function render()
    {
        return view('livewire.diagnostic-walk');
    }
}