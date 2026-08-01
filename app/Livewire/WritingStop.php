<?php

namespace App\Livewire;

use App\Jobs\ScoreWritingSubmission;
use App\Models\WritingPrompt;
use App\Models\WritingSubmission;
use App\Services\Writing\WritingScorer;
use App\Services\Writing\WritingScoringUnavailable;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * The Writer's Log — the parallel writing track's home. Shows this week's prompt,
 * takes the student's draft, and returns a warm four-criterion rubric. When the AI
 * scorer is unavailable the submission is saved and queued, and she is told her
 * feedback is on its way (WR-01/02/03).
 */
#[Layout('components.layouts.diagnostic')]
class WritingStop extends Component
{
    public ?WritingPrompt $prompt = null;

    public string $body = '';

    public ?WritingSubmission $submission = null;

    /** True once a submission was saved but scoring is queued (AI outage, WR-03). */
    public bool $queued = false;

    public function mount(): void
    {
        $this->prompt = WritingPrompt::forWeek();

        // Show her most recent submission for this week's prompt, if any.
        if ($this->prompt !== null) {
            $this->submission = WritingSubmission::query()
                ->where('student_id', auth()->id())
                ->where('writing_prompt_id', $this->prompt->id)
                ->latest()
                ->first();

            $this->queued = $this->submission !== null && ! $this->submission->isScored();
        }
    }

    public function submit(WritingScorer $scorer): void
    {
        $this->validate([
            'body' => ['required', 'string', 'min:20'],
        ], [
            'body.required' => 'Write a little something before you send it in.',
            'body.min' => 'Try writing a bit more before you send it in.',
        ]);

        abort_if($this->prompt === null, 404);

        $submission = WritingSubmission::create([
            'student_id' => auth()->id(),
            'writing_prompt_id' => $this->prompt->id,
            'body' => $this->body,
            'status' => WritingSubmission::STATUS_PENDING,
        ]);

        try {
            $submission->applyRubric($scorer->score($submission));
            $this->queued = false;
        } catch (WritingScoringUnavailable) {
            // Saved and queued — she is told her feedback is on its way (WR-03).
            ScoreWritingSubmission::dispatch($submission);
            $this->queued = true;
        }

        $this->submission = $submission->fresh();
    }

    public function render()
    {
        return view('livewire.writing-stop');
    }
}
