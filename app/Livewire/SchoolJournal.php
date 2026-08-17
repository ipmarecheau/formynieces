<?php

namespace App\Livewire;

use App\Models\SchoolJournalEntry;
use App\Models\User;
use App\Services\SchoolJournal\OcrService;
use App\Services\SchoolJournal\SchoolEvidenceService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

/**
 * SJ-01..04/09 — the guardian's school journal: upload a graded paper (photo or
 * PDF), let the OCR seam digitise it, confirm/correct what it read, and read
 * the term timeline + per-strand trend.
 */
class SchoolJournal extends Component
{
    use WithFileUploads;

    public User $student;

    /** @var TemporaryUploadedFile|null */
    public $paper = null;

    public ?int $confirmingId = null;

    public array $form = [];

    public string $uploadNote = '';

    public function mount(User $student): void
    {
        abort_unless($student->isStudent() && $student->parent_id === auth()->id(), 403);

        $this->student = $student;
    }

    /** SJ-01 — store the upload; SJ-07 — digitise when the seam can. */
    public function savePaper(OcrService $ocr): void
    {
        $this->validate([
            'paper' => ['required', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:10240'],
        ]);

        $path = $this->paper->store("school-journal/{$this->student->id}", 'local');

        $entry = SchoolJournalEntry::create([
            'student_id' => $this->student->id,
            'uploaded_by' => 'guardian',
            'image_path' => $path,
            'assessment_date' => now()->toDateString(),
            'digitisation_status' => SchoolJournalEntry::STATUS_PENDING,
        ]);

        $result = $ocr->digitize(Storage::disk('local')->path($path), $this->paper->getMimeType());

        if ($result !== null) {
            $entry->fill($result['fields']);
            $entry->ocr_text = $result['text'];
            $entry->ocr_confidence = $result['confidence'];
            $entry->digitisation_status = SchoolJournalEntry::STATUS_DIGITISED;
            $entry->save();

            if ($result['review'] !== []) {
                $this->uploadNote = 'Filed — but I was not sure about: '.implode(', ', $result['review']).'. Please check them.';
                $this->beginConfirmation($entry->id);
            } else {
                $this->uploadNote = 'Filed and read. Please give it a quick look and confirm.';
                $this->beginConfirmation($entry->id);
            }
        } else {
            $this->uploadNote = 'Filed. I could not read this one automatically — please enter the details by hand.';
            $this->beginConfirmation($entry->id);
        }

        $this->reset('paper');
    }

    public function beginConfirmation(int $entryId): void
    {
        $entry = SchoolJournalEntry::where('student_id', $this->student->id)->findOrFail($entryId);

        $this->confirmingId = $entry->id;
        $this->form = [
            'assessment_date' => $entry->assessment_date?->format('Y-m-d'),
            'term' => $entry->term,
            'subject' => $entry->subject,
            'strand' => $entry->strand,
            'assessment_type' => $entry->assessment_type,
            'score' => $entry->score,
            'teacher_comment' => $entry->teacher_comment,
        ];
    }

    /** SJ-02 — the guardian corrects whatever the pipeline read wrongly, then confirms. */
    public function confirmEntry(SchoolEvidenceService $evidence): void
    {
        $entry = SchoolJournalEntry::where('student_id', $this->student->id)->findOrFail($this->confirmingId);

        $validated = $this->validate([
            'form.assessment_date' => ['required', 'date'],
            'form.term' => ['nullable', 'string', 'max:60'],
            'form.subject' => ['nullable', 'string', 'max:60'],
            'form.strand' => ['nullable', 'string', 'max:60'],
            'form.assessment_type' => ['nullable', 'string', 'max:60'],
            'form.score' => ['nullable', 'string', 'max:30'],
            'form.teacher_comment' => ['nullable', 'string', 'max:1000'],
        ])['form'];

        $entry->fill($validated);
        $entry->digitisation_status = SchoolJournalEntry::STATUS_CONFIRMED;
        $entry->save();

        $evidence->recordSignals($entry); // SJ-08 — the corroborating/weakness signal

        $this->reset('confirmingId', 'form');
    }

    public function deleteEntry(int $entryId): void
    {
        SchoolJournalEntry::where('student_id', $this->student->id)
            ->where('digitisation_status', '!=', SchoolJournalEntry::STATUS_CONFIRMED)
            ->whereKey($entryId)
            ->delete();
    }

    /** SJ-03 — newest first, grouped by term. */
    public function groupedEntries(): Collection
    {
        return SchoolJournalEntry::where('student_id', $this->student->id)
            ->orderByDesc('assessment_date')
            ->get()
            ->groupBy(fn (SchoolJournalEntry $entry) => $entry->term ?: 'Unlabelled');
    }

    public function lowConfidence(SchoolJournalEntry $entry, string $field): bool
    {
        $confidence = $entry->ocr_confidence[$field] ?? null;

        return $confidence !== null && (float) $confidence < 0.70;
    }

    public function render(SchoolEvidenceService $evidence)
    {
        return view('livewire.school-journal', [
            'grouped' => $this->groupedEntries(),
            'trend' => $evidence->trendByTerm($this->student->id),
        ]);
    }
}
