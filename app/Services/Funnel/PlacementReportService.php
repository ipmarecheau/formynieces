<?php

namespace App\Services\Funnel;

use App\Mail\PlacementReportMail;
use App\Models\Lead;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Builds and delivers the first-choice placement report (lead_capture.feature LG-04/05).
 * The honest-layer value — where the child stands, their three weakest strands, and the
 * single next step — delivered before the parent ever pays.
 */
class PlacementReportService
{
    /**
     * Grade a set of mock answers into the report shape.
     *
     * @param  Collection<int, array{strand:string, correct:bool}>  $graded
     * @return array{score:int, band:string, weakest_strands:array<int,string>, next_step:string}
     */
    public function compute(Collection $graded): array
    {
        $total = max(1, $graded->count());
        $correct = $graded->where('correct', true)->count();
        $score = (int) round($correct / $total * 100);

        // Weakest strands: those with the most misses, worst first, up to three.
        $weakest = $graded->where('correct', false)
            ->groupBy('strand')
            ->map->count()
            ->sortDesc()
            ->keys()
            ->take(3)
            ->values()
            ->all();

        return [
            'score' => $score,
            'band' => $this->band($score),
            'weakest_strands' => $weakest,
            'next_step' => $this->nextStep($weakest),
        ];
    }

    /** The projected first-choice readiness band for a score. */
    public function band(int $score): string
    {
        return match (true) {
            $score >= 80 => 'On track for their first-choice school',
            $score >= 60 => 'Within reach with a steady push',
            default => 'A real catch-up is needed — but there’s time',
        };
    }

    /** @param  array<int,string>  $weakest */
    private function nextStep(array $weakest): string
    {
        if ($weakest === []) {
            return 'Keep the momentum — practise a little every day to stay first-choice ready.';
        }

        return 'Start with '.$weakest[0].' — it’s the single biggest lever right now.';
    }

    /**
     * Persist a computed report onto the lead.
     *
     * @param  array{score:int, band:string, weakest_strands:array<int,string>, next_step:string}  $report
     */
    public function persist(Lead $lead, array $report): void
    {
        $lead->update([
            'mock_score' => $report['score'],
            'placement_band' => $report['band'],
            'weakest_strands' => $report['weakest_strands'],
            'next_step' => $report['next_step'],
        ]);
    }

    /** Email the report to the parent (LG-05). Delivery is email only; a mail hiccup
     *  must never break the funnel, so failures are logged, not thrown. */
    public function deliver(Lead $lead, array $pdfAttachments = []): void
    {
        try {
            Mail::to($lead->email)->send(new PlacementReportMail($lead, $pdfAttachments));
        } catch (\Throwable $e) {
            Log::warning('Placement report email failed: '.$e->getMessage());
        }
    }
}
