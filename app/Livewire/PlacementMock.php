<?php

namespace App\Livewire;

use App\Models\Lead;
use App\Models\PracticeQuestion;
use App\Services\Funnel\PlacementReportService;
use Illuminate\Support\Collection;
use Livewire\Component;

/**
 * The free SEA mock (lead_capture.feature LG-03) — a short set drawn from the SEA-aligned
 * practice bank, answered one at a time. On completion it grades the answers, writes the
 * placement report onto the lead, emails it (LG-05), and tells the parent to view it.
 */
class PlacementMock extends Component
{
    public int $leadId;

    /** Served questions, display-safe: id, prompt, options, strand (correct_index kept server-side via id). */
    public array $questions = [];

    public int $index = 0;

    /** questionId => chosenIndex */
    public array $answers = [];

    public function mount(int $leadId): void
    {
        $this->leadId = $leadId;

        $count = (int) config('funnel.mock_questions', 8);
        $served = PracticeQuestion::query()
            ->where('is_active', true)
            ->inRandomOrder()
            ->take($count)
            ->get();

        $this->questions = $served->map(fn (PracticeQuestion $q): array => [
            'id' => $q->id,
            'prompt' => strip_tags((string) $q->prompt),
            'options' => array_values($q->options ?? []),
            'strand' => $q->strand ?: ($q->sea_section ?: $q->subject),
        ])->all();
    }

    public function answer(int $chosenIndex): void
    {
        if (! isset($this->questions[$this->index])) {
            return;
        }

        $this->answers[$this->questions[$this->index]['id']] = $chosenIndex;

        if ($this->index + 1 < count($this->questions)) {
            $this->index++;

            return;
        }

        $this->finish();
    }

    private function finish(): void
    {
        $served = PracticeQuestion::whereIn('id', array_keys($this->answers))->get()->keyBy('id');

        /** @var Collection<int, array{strand:string, correct:bool}> $graded */
        $graded = collect($this->answers)->map(function (int $chosen, int $qId) use ($served) {
            $q = $served[$qId] ?? null;

            return [
                'strand' => $q ? ($q->strand ?: ($q->sea_section ?: $q->subject)) : 'General',
                'correct' => $q !== null && $chosen === (int) $q->correct_index,
            ];
        })->values();

        $service = app(PlacementReportService::class);
        $lead = Lead::find($this->leadId);
        $report = $service->compute($graded);
        $service->persist($lead, $report);
        $service->deliver($lead->fresh());

        $this->dispatch('mock-complete');
    }

    public function render()
    {
        return view('livewire.placement-mock');
    }
}
