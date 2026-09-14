<x-filament-panels::page>
<style>
    .ppd-toolbar{display:flex;gap:.75rem;align-items:center;margin-bottom:1rem;flex-wrap:wrap}.ppd-select{width:min(100%,520px);border:1px solid #cbd5e1;border-radius:.5rem;padding:.6rem;background:transparent}.ppd-count{font-size:.85rem;color:#64748b}.ppd-grid{display:grid;grid-template-columns:minmax(0,1.05fr) minmax(360px,.95fr);gap:1rem;align-items:start}.ppd-source,.ppd-review{border:1px solid #cbd5e1;border-radius:.85rem;background:#f8fafc}.ppd-source{position:sticky;top:1rem;height:calc(100vh - 11rem);min-height:560px;overflow-y:auto;padding:1rem}.ppd-source-title{position:sticky;top:-1rem;z-index:2;background:#f8fafc;padding:0 0 .7rem;font-size:.85rem;font-weight:700;color:#475569}.ppd-page{scroll-margin-top:1rem;margin:0 0 1.4rem}.ppd-page:last-child{margin-bottom:0}.ppd-page-label{font-size:.75rem;font-weight:700;color:#64748b;margin:0 0 .4rem}.ppd-page img{display:block;width:100%;background:#fff;box-shadow:0 1px 4px #94a3b8}.ppd-review{padding:1rem;min-height:560px}.ppd-nav{display:flex;align-items:center;justify-content:space-between;gap:.6rem;border-bottom:1px solid #e2e8f0;padding-bottom:.85rem;margin-bottom:1rem}.ppd-nav button,.ppd-source-link{border:1px solid #cbd5e1;border-radius:.5rem;background:#fff;padding:.45rem .7rem;font-size:.84rem;font-weight:700;color:#0f766e}.ppd-nav button:disabled{opacity:.45;cursor:not-allowed}.ppd-progress{font-size:.84rem;color:#475569;text-align:center}.ppd-card textarea,.ppd-card input{width:100%;border:1px solid #cbd5e1;border-radius:.5rem;padding:.6rem;background:#fff;margin-top:.3rem}.ppd-card textarea{min-height:120px}.ppd-label{font-size:.72rem;text-transform:uppercase;letter-spacing:.05em;font-weight:700;color:#64748b;display:block;margin-top:.85rem}.ppd-meta{font-size:.85rem;color:#64748b;line-height:1.5}.ppd-svg{margin-top:.3rem;border:1px solid #e2e8f0;border-radius:.5rem;background:#fff;padding:.7rem;max-height:300px;overflow:auto}.ppd-svg svg{display:block;max-width:100%;height:auto;margin:auto}.ppd-empty{padding:2rem;text-align:center;color:#64748b}@media(max-width:900px){.ppd-grid{grid-template-columns:1fr}.ppd-source{position:relative;top:auto;height:65vh;min-height:420px}.ppd-review{min-height:auto}}
</style>

@if($draftFiles === [])
    <x-filament::section><p>No extraction drafts are available yet. Run <code>past-papers:extract ... --write</code> first.</p></x-filament::section>
@else
    @php($questions = $draft['draft']['questions'] ?? [])
    @php($question = $questions[$reviewIndex] ?? [])
    <div class="ppd-toolbar">
        <label for="draft-select" class="ppd-label" style="margin:0;min-width:100px">Source draft</label>
        <select id="draft-select" wire:model.live="selectedDraft" class="ppd-select">@foreach($draftFiles as $file)<option value="{{ $file }}">{{ $file }}</option>@endforeach</select>
        <span class="ppd-count">{{ count($questions) }} extracted questions · {{ $sourcePageCount }} source pages</span>
    </div>

    <div class="ppd-grid">
        <aside class="ppd-source" aria-label="Scrollable source paper">
            <div class="ppd-source-title">Scrollable source paper · use the question controls to jump to the matching page</div>
            @for($page = 1; $page <= $sourcePageCount; $page++)
                <figure id="source-page-{{ $page }}" class="ppd-page">
                    <figcaption class="ppd-page-label">Source page {{ $page }}{{ $page === $sourcePage ? ' · current question' : '' }}</figcaption>
                    <img loading="lazy" alt="Source paper page {{ $page }}" src="{{ $this->sourcePageUrl($page) }}">
                </figure>
            @endfor
        </aside>

        <section class="ppd-review" aria-label="Question review queue">
            <div class="ppd-nav">
                <button type="button" wire:click="previousQuestion" @disabled($reviewIndex <= 0)>← Previous</button>
                <div class="ppd-progress"><strong>Question {{ $question['number'] ?? '—' }}</strong><br>{{ count($questions) ? $reviewIndex + 1 : 0 }} of {{ count($questions) }}</div>
                <button type="button" wire:click="nextQuestion" @disabled($reviewIndex >= count($questions) - 1)>Next →</button>
            </div>

            @if($question === [])
                <p class="ppd-empty">This draft has no questions to review.</p>
            @else
                <p class="ppd-meta">Compare this question with the source paper, correct it if needed, then continue. Every item remains in review until imported and approved.</p>
                <button type="button" class="ppd-source-link" wire:click="goToQuestion({{ $reviewIndex }})" onclick="document.getElementById('source-page-{{ (int) ($question['source_page'] ?? 1) }}')?.scrollIntoView({behavior:'smooth',block:'start'})">View source page {{ (int) ($question['source_page'] ?? 1) }}</button>
                <span class="ml-2 text-xs text-amber-600">{{ ($question['needs_review'] ?? true) ? 'Needs review' : 'Ready for QC' }}</span>

                <div class="ppd-card">
                    <label class="ppd-label">Prompt</label>
                    <textarea wire:model.defer="draft.draft.questions.{{ $reviewIndex }}.prompt"></textarea>
                    <div class="grid grid-cols-2 gap-3">
                        <div><label class="ppd-label">SEA topic</label><input wire:model.defer="draft.draft.questions.{{ $reviewIndex }}.topic"></div>
                        <div><label class="ppd-label">Difficulty</label><input type="number" min="1" max="5" wire:model.defer="draft.draft.questions.{{ $reviewIndex }}.difficulty"></div>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div><label class="ppd-label">Answer</label><input wire:model.defer="draft.draft.questions.{{ $reviewIndex }}.correct_answer"></div>
                        <div><label class="ppd-label">Source page</label><input type="number" min="1" max="{{ $sourcePageCount }}" wire:model.defer="draft.draft.questions.{{ $reviewIndex }}.source_page"></div>
                    </div>
                    <label class="ppd-label">Illustration SVG</label>
                    @if($svg = $this->previewSvg($question['illustration_svg'] ?? null))
                        <div class="ppd-svg" aria-label="SVG illustration preview">{!! $svg !!}</div>
                    @else
                        <p class="ppd-meta">No source illustration is attached to this question.</p>
                    @endif
                    <textarea aria-label="Illustration SVG question {{ $question['number'] ?? $reviewIndex + 1 }}" style="min-height:100px" wire:model.defer="draft.draft.questions.{{ $reviewIndex }}.illustration_svg"></textarea>
                    <label class="mt-3 flex items-center gap-2 text-sm"><input type="checkbox" wire:model.defer="draft.draft.questions.{{ $reviewIndex }}.needs_review"> Keep in review queue</label>
                </div>
            @endif
        </section>
    </div>
@endif

<script>
document.addEventListener('livewire:init', () => {
    Livewire.on('past-paper-source-focus', ({ page }) => {
        document.getElementById(`source-page-${page}`)?.scrollIntoView({ behavior: 'smooth', block: 'start' });
    });
});
</script>
</x-filament-panels::page>
