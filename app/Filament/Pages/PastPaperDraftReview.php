<?php

namespace App\Filament\Pages;

use App\Models\PastPaper;
use App\Models\PastPaperQuestion;
use App\Models\SyllabusModule;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Storage;
use App\Services\PastPapers\PastPaperTopicMapper;

class PastPaperDraftReview extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentMagnifyingGlass;
    protected static ?string $navigationLabel = 'Past Paper Drafts';
    protected static ?string $title = 'Past Paper Draft Review';
    protected string $view = 'filament.pages.past-paper-draft-review';

    public array $draftFiles = [];
    public ?string $selectedDraft = null;
    public int $sourcePage = 1;
    public int $sourcePageCount = 1;
    /** @var array<string, mixed> */
    public array $draft = [];

    public function mount(): void
    {
        $this->refreshFiles();
        $this->selectedDraft = collect($this->draftFiles)
            ->sortByDesc(fn (string $file) => Storage::disk('local')->lastModified('past-paper-source/extractions/'.$file))
            ->first();
        $this->loadDraft();
    }

    public function updatedSelectedDraft(): void
    {
        $this->sourcePage = 1;
        $this->loadDraft();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('refresh')->label('Refresh drafts')->icon(Heroicon::ArrowPath)->action(function (): void {
                $this->refreshFiles(); $this->loadDraft();
            }),
            Action::make('save')->label('Save draft')->icon(Heroicon::Check)->action(fn () => $this->saveDraft()),
            Action::make('map')->label('Map SEA metadata')->icon(Heroicon::OutlinedTag)->action(fn (PastPaperTopicMapper $mapper) => $this->mapTopics($mapper)),
            Action::make('import')->label('Import as unapproved')->icon(Heroicon::ArrowDownTray)->requiresConfirmation()->action(fn () => $this->importDraft()),
        ];
    }

    private function refreshFiles(): void
    {
        $this->draftFiles = collect(Storage::disk('local')->files('past-paper-source/extractions'))
            ->filter(fn (string $path) => str_ends_with($path, '.json'))->map(fn (string $path) => basename($path))->sort()->values()->all();
    }

    private function loadDraft(): void
    {
        if (! $this->selectedDraft || ! in_array($this->selectedDraft, $this->draftFiles, true)) { $this->draft = []; return; }
        $path = 'past-paper-source/extractions/'.$this->selectedDraft;
        $this->draft = json_decode(Storage::disk('local')->get($path), true) ?: [];
        $manifest = 'past-paper-source/manifest.json';
        $manifestData = $manifest && Storage::disk('local')->exists($manifest) ? json_decode(Storage::disk('local')->get($manifest), true) : [];
        $source = collect($manifestData['files'] ?? [])->firstWhere('filename', $this->draft['filename'] ?? '');
        $this->sourcePageCount = (int) ($source['pages'] ?? $this->draft['pages'] ?? 1);
    }

    public function saveDraft(): void
    {
        if (! $this->selectedDraft) return;
        Storage::disk('local')->put('past-paper-source/extractions/'.$this->selectedDraft, json_encode($this->draft, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));
        Notification::make()->title('Draft saved')->success()->send();
    }

    public function mapTopics(PastPaperTopicMapper $mapper): void
    {
        $this->draft = $mapper->map($this->draft);
        $this->saveDraft();
        Notification::make()->title('SEA metadata mapped')->body('Questions remain unapproved until final QC.')->success()->send();
    }

    public function previousPage(): void { $this->sourcePage = max(1, $this->sourcePage - 1); }
    public function nextPage(): void { $this->sourcePage = min($this->sourcePageCount, $this->sourcePage + 1); }

    public function importDraft(): void
    {
        $this->saveDraft();
        $paperData = (array) ($this->draft['draft']['paper'] ?? []);
        $filename = (string) ($this->draft['filename'] ?? $this->selectedDraft ?? 'source');
        $paper = PastPaper::updateOrCreate(['source_ref' => sha1($filename)], [
            'title' => pathinfo($filename, PATHINFO_FILENAME), 'subject' => $paperData['subject'] ?? 'ELA', 'provenance' => 'real', 'is_published' => false,
        ]);
        $count = 0;
        foreach ((array) ($this->draft['draft']['questions'] ?? []) as $row) {
            $module = $this->moduleFor($row);
            $needsReview = (bool) ($row['needs_review'] ?? true) || blank($row['correct_answer'] ?? null);
            $number = (int) ($row['number'] ?? ($count + 1));
            PastPaperQuestion::updateOrCreate(['past_paper_id' => $paper->id, 'number' => $number], [
                'syllabus_module_id' => $module?->id, 'item_type' => empty($row['options']) ? 'extended' : 'mcq', 'prompt' => (string) ($row['prompt'] ?? ''),
                'options' => $row['options'] ?? null, 'illustration_svg' => $this->safeSvg($row['illustration_svg'] ?? null), 'correct_answer' => $row['correct_answer'] ?? null, 'marks' => (int) ($row['marks'] ?? 1),
                'difficulty' => (int) ($row['difficulty'] ?? 3), 'provenance' => 'real', 'qc_status' => $needsReview ? 'unapproved' : 'approved',
                'qc_reason' => $needsReview ? 'Imported draft requires answer/topic/editor review.' : null,
            ]);
            $count++;
        }
        Notification::make()->title('Draft imported')->body("{$count} questions are now in the Paper Questions queue for QC.")->success()->persistent()->send();
    }

    private function moduleFor(array $row): ?SyllabusModule
    {
        if (! empty($row['module_code'])) return SyllabusModule::where('code', $row['module_code'])->first();
        $topic = trim((string) ($row['topic'] ?? ''));
        return $topic === '' ? null : SyllabusModule::where('topic', 'like', '%'.$topic.'%')->first();
    }

    private function safeSvg(mixed $svg): ?string
    {
        if (! is_string($svg) || trim($svg) === '' || ! str_starts_with(trim($svg), '<svg')) return null;
        $svg = preg_replace('/<\/?(script|iframe|object|embed|foreignObject)[^>]*>/i', '', $svg) ?? '';
        $svg = preg_replace('/\s(?:on[a-z]+|href|xlink:href)\s*=\s*(["\']).*?\1/i', '', $svg) ?? '';
        return strlen($svg) <= 20000 ? trim($svg) : null;
    }

    public function sourceUrl(): ?string
    {
        return $this->selectedDraft ? route('admin.past-paper-drafts.source', ['draft' => $this->selectedDraft]) : null;
    }

    public function sourcePageUrl(): ?string
    {
        return $this->selectedDraft ? route('admin.past-paper-drafts.page', ['draft' => $this->selectedDraft, 'page' => $this->sourcePage]) : null;
    }
}
