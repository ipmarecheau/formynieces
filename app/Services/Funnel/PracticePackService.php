<?php

namespace App\Services\Funnel;

use App\Models\PracticeQuestion;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * The free AI Practice Pack (lead_capture.feature LG-09) — 30 fresh, past-paper-style
 * questions with worked solutions, drawn from the SEA-aligned bank and rendered as a
 * branded PDF booklet the parent can download / receive by email.
 */
class PracticePackService
{
    /**
     * Assemble the pack's questions.
     *
     * @return Collection<int, PracticeQuestion>
     */
    public function assemble(?int $count = null): Collection
    {
        $count ??= (int) config('funnel.pack_questions', 30);

        return PracticeQuestion::query()
            ->where('is_active', true)
            ->inRandomOrder()
            ->take($count)
            ->get();
    }

    /**
     * Render the pack to a PDF on disk and return its absolute path.
     */
    public function renderPdf(?string $childLevel = null): string
    {
        $questions = $this->assemble();

        $pdf = Pdf::loadView('pdf.practice-pack', [
            'questions' => $questions,
            'childLevel' => $childLevel,
            'generatedAt' => now(),
        ])->setPaper('a4');

        $relative = 'packs/sea-practice-pack-'.Str::random(12).'.pdf';
        Storage::disk('local')->put($relative, $pdf->output());

        return Storage::disk('local')->path($relative);
    }
}
