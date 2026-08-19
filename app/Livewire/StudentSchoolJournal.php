<?php

namespace App\Livewire;

use App\Models\SchoolJournalEntry;
use App\Services\SchoolJournal\JournalDigitiser;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

/**
 * SJ-01/SJ-06 — the student's own view of the school journal: she can file her
 * graded papers into the same journal, but her screen is deliberately calm and
 * score-free. What the paper said stays in the honest layer with her guardian;
 * her world only knows the paper was filed and it helps Smooth steer.
 */
#[Layout('components.layouts.diagnostic')]
class StudentSchoolJournal extends Component
{
    use WithFileUploads;

    /** @var TemporaryUploadedFile|null */
    public $paper = null;

    public string $note = '';

    public function savePaper(JournalDigitiser $digitiser): void
    {
        $this->validate([
            'paper' => ['required', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:10240'],
        ]);

        $student = auth()->user();

        $path = $this->paper->store("school-journal/{$student->id}", 'local');

        $entry = SchoolJournalEntry::create([
            'student_id' => $student->id,
            'uploaded_by' => 'student',
            'image_path' => $path,
            'assessment_date' => now()->toDateString(),
            'digitisation_status' => SchoolJournalEntry::STATUS_PENDING,
        ]);

        // Digitise silently when possible — but never surface scores or
        // reasoning here; her world stays clean (SJ-06/SJ-13).
        $digitiser->digitise($entry, $this->paper->getMimeType());

        $this->note = 'Paper filed! Smooth tucked it into your journal — it helps him steer your voyage. 🐢';
        $this->reset('paper');
    }

    public function render()
    {
        return view('livewire.student-school-journal', [
            'entries' => SchoolJournalEntry::where('student_id', auth()->id())
                ->orderByDesc('assessment_date')
                ->limit(20)
                ->get(['id', 'assessment_date', 'digitisation_status']),
        ]);
    }
}
