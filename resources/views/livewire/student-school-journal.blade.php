<div class="fmn-page" style="max-width: 640px;">
    <h1 class="fmn-section-title">🐢 Your school papers journal</h1>
    <p style="color: var(--fmn-muted, #93b2cc); font-size: .95rem; margin-bottom: 1.25rem;">
        Got a marked paper back from school? File it here! Smooth reads it so your voyage can learn
        from it too. You never have to look at a grade on this page — just sail on.
    </p>

    <div class="fmn-card">
        <form wire:submit="savePaper" class="flex flex-col gap-3">
            <input type="file" wire:model="paper" accept="image/*,.pdf" class="text-sm">
            @error('paper')<p class="text-red-400 text-sm">{{ $message }}</p>@enderror
            <button type="submit" class="fmn-btn fmn-btn-primary self-start" wire:loading.attr="disabled" wire:target="paper">
                <span wire:loading.remove wire:target="paper">File my paper 📎</span>
                <span wire:loading wire:target="paper">Tucking it in…</span>
            </button>
        </form>
        @if ($note)
            <p class="mt-3 text-sm font-bold" style="color: #5eead4;">{{ $note }}</p>
        @endif
    </div>

    <div class="fmn-card">
        <h2 class="fmn-section-title">📎 Papers you filed</h2>
        @forelse ($entries as $e)
            <div class="flex items-center gap-3 rounded-xl px-3 py-2 mb-2"
                 style="background: rgba(12,36,64,.6); border: 1.5px solid rgba(103,232,249,.2);">
                <span>📄</span>
                <span class="text-sm" style="color: #e6f2fb;">Filed {{ $e->assessment_date?->format('j M Y') }}</span>
            </div>
        @empty
            <p class="text-sm" style="color: var(--fmn-muted, #93b2cc);">Nothing filed yet — your first paper will live here.</p>
        @endforelse
    </div>
</div>
