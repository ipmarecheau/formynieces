<div class="fmn-page" style="max-width: 860px;">
    <h1 class="fmn-section-title">🏫 School Journal — {{ $student->name }}</h1>
    <p style="color: var(--fmn-muted, #93b2cc); font-size: .95rem; margin-bottom: 1.25rem;">
        Graded papers from school, kept beside her voyage. Strong results quietly confirm what she knows;
        wobbly ones gently steer her plan. Nothing here ever gates or judges her.
    </p>

    {{-- Upload --}}
    <div class="fmn-card">
        <h2 class="fmn-section-title">📄 File a paper</h2>
        <form wire:submit="savePaper" class="flex flex-col gap-3">
            <input type="file" wire:model="paper" accept="image/*,.pdf" class="text-sm">
            @error('paper')<p class="text-red-400 text-sm">{{ $message }}</p>@enderror
            <button type="submit" class="fmn-btn fmn-btn-primary self-start" wire:loading.attr="disabled" wire:target="paper">
                <span wire:loading.remove wire:target="paper">File it</span>
                <span wire:loading wire:target="paper">Reading it…</span>
            </button>
        </form>
        @if ($uploadNote)
            <p class="mt-3 text-sm font-bold" style="color: #5eead4;">{{ $uploadNote }}</p>
        @endif
    </div>

    {{-- Confirm / correct (SJ-02) --}}
    @if ($confirmingId)
        @php($entry = App\Models\SchoolJournalEntry::find($confirmingId))
        <div class="fmn-card" style="border-color: #fcd34d;">
            <h2 class="fmn-section-title">✅ Check what was read — then confirm</h2>
            <p class="text-sm mb-3" style="color: var(--fmn-muted, #93b2cc);">
                Fields the reader was unsure about are outlined in gold. Fix anything wrong — you are the
                final say, always.
            </p>
            <form wire:submit="confirmEntry" class="flex flex-col gap-3">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    @foreach ([
                        'assessment_date' => ['Date of assessment', 'date'],
                        'term' => ['Term (e.g. Term I 2026)', 'text'],
                        'subject' => ['Subject', 'text'],
                        'strand' => ['Strand (e.g. Grammar, Number)', 'text'],
                        'assessment_type' => ['Assessment type (test, essay…)', 'text'],
                        'score' => ['Score as written (e.g. 18/20)', 'text'],
                    ] as $field => [$label, $type])
                        <label class="flex flex-col gap-1 text-sm font-bold" for="form-{{ $field }}"
                               style="@if($entry && $this->lowConfidence($entry, $field)) outline: 2px solid #fcd34d; outline-offset: 2px; border-radius: 8px; padding: 4px; @endif">
                            {{ $label }}
                            <input id="form-{{ $field }}" type="{{ $type }}" wire:model="form.{{ $field }}"
                                   class="rounded-lg" style="background: rgba(12,36,64,.7); border: 1.5px solid rgba(103,232,249,.3); color: #e6f2fb; padding: 8px 10px;">
                        </label>
                    @endforeach
                </div>
                <label class="flex flex-col gap-1 text-sm font-bold">Teacher's comment
                    <textarea wire:model="form.teacher_comment" rows="2" class="rounded-lg"
                              style="background: rgba(12,36,64,.7); border: 1.5px solid rgba(103,232,249,.3); color: #e6f2fb; padding: 8px 10px;"></textarea>
                </label>
                @error('form.*')<p class="text-red-400 text-sm">{{ $message }}</p>@enderror
                <div class="flex gap-2">
                    <button type="submit" class="fmn-btn fmn-btn-primary">Confirm entry</button>
                    <button type="button" class="fmn-btn fmn-btn-ghost" wire:click="$set('confirmingId', null)">Later</button>
                </div>
            </form>
        </div>
    @endif

    {{-- Term timeline (SJ-03) --}}
    <div class="fmn-card">
        <h2 class="fmn-section-title">📘 The journal</h2>
        @forelse ($grouped as $term => $entries)
            <p class="text-sm font-extrabold uppercase tracking-wide mb-2" style="color: #f6b71e;">{{ $term }}</p>
            <div class="flex flex-col gap-2 mb-4">
                @foreach ($entries as $e)
                    <div class="flex items-center justify-between gap-3 rounded-xl px-3 py-2"
                         style="background: rgba(12,36,64,.6); border: 1.5px solid rgba(103,232,249,.2);">
                        <div class="text-sm">
                            <span class="font-bold" style="color: #e6f2fb;">{{ $e->assessment_date?->format('j M Y') }}</span>
                            <span style="color: #93b2cc;"> · {{ $e->strand ?? 'strand not set' }} · {{ $e->assessment_type ?? '' }}</span>
                        </div>
                        <div class="flex items-center gap-2 text-sm">
                            <span class="font-bold" style="color: #5eead4;">{{ $e->score ?? '—' }}</span>
                            @if ($e->digitisation_status === App\Models\SchoolJournalEntry::STATUS_CONFIRMED)
                                <span class="fmn-badge" style="background: rgba(16,185,129,.2); color: #6ee7b7;">confirmed</span>
                            @elseif ($e->digitisation_status === App\Models\SchoolJournalEntry::STATUS_DIGITISED)
                                <button type="button" class="fmn-btn fmn-btn-sm" wire:click="beginConfirmation({{ $e->id }})">Check it</button>
                            @else
                                <button type="button" class="fmn-btn fmn-btn-sm" wire:click="beginConfirmation({{ $e->id }})">Enter details</button>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @empty
            <p class="text-sm" style="color: var(--fmn-muted, #93b2cc);">No papers filed yet — the first one goes here.</p>
        @endforelse
    </div>

    {{-- Trend (SJ-09) --}}
    @if ($trend !== [])
        <div class="fmn-card">
            <h2 class="fmn-section-title">📈 School picture by term</h2>
            @foreach ($trend as $termBlock)
                <p class="text-sm font-extrabold uppercase tracking-wide mb-1" style="color: #f6b71e;">{{ $termBlock['term'] }}</p>
                <ul class="text-sm mb-3" style="color: #93b2cc;">
                    @foreach ($termBlock['strands'] as $s)
                        <li class="ml-4 list-disc">{{ $s['strand'] }} — {{ $s['score'] }} ({{ $s['assessment'] }})</li>
                    @endforeach
                </ul>
            @endforeach
        </div>
    @endif
</div>
