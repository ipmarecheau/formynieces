<div class="fmn-page sj" style="max-width: 820px;">
    <style>
        .sj { --sj-ink:#1f2937; --sj-muted:#6b7280; --sj-teal:#0e7490; --sj-line:#e6eef5; }
        .sj-head { margin-bottom: 1.25rem; }
        .sj-title { font-family:'Fredoka One',cursive; font-size:1.5rem; color:var(--sj-teal); margin:0 0 .3rem; }
        .sj-lede { color:var(--sj-muted); font-size:.92rem; line-height:1.55; margin:0; }
        .sj-card {
            background:#fff; border:1.5px solid var(--sj-line); border-radius:18px;
            padding:1.15rem 1.3rem; margin-bottom:1rem; box-shadow:0 1px 2px rgba(15,42,64,.04);
        }
        .sj-card-gold { border-color:#fcd34d; background:#fffdf5; }
        .sj-h2 { font-family:'Fredoka One',cursive; font-size:1.05rem; color:var(--sj-teal); margin:0 0 .8rem; display:flex; align-items:center; gap:.45rem; }
        .sj-note { color:var(--sj-muted); font-size:.85rem; line-height:1.5; margin:0 0 .9rem; }
        .sj-ok-note { color:#0f766e; font-weight:700; font-size:.85rem; margin:.75rem 0 0; }

        /* upload */
        .sj-file { font-size:.85rem; color:var(--sj-ink); }
        .sj-file::file-selector-button {
            font-family:'Nunito',sans-serif; font-weight:700; font-size:.82rem; cursor:pointer;
            border:1.5px solid #cbe4f0; background:#eff6ff; color:var(--sj-teal);
            border-radius:999px; padding:.4rem .9rem; margin-right:.7rem;
        }
        .sj-btn {
            font-family:'Nunito',sans-serif; font-weight:800; font-size:.85rem; cursor:pointer;
            border:none; border-radius:999px; padding:.55rem 1.2rem; color:#fff;
            background:linear-gradient(135deg,#0e7490,#0891b2);
        }
        .sj-btn:disabled { opacity:.6; cursor:default; }
        .sj-btn-ghost { background:#fff; color:var(--sj-teal); border:1.5px solid #cbe4f0; }
        .sj-btn-sm { padding:.35rem .8rem; font-size:.78rem; }
        .sj-err { color:#dc2626; font-size:.82rem; margin:.4rem 0 0; }

        /* confirm form */
        .sj-grid { display:grid; grid-template-columns:1fr 1fr; gap:.8rem; }
        @media (max-width:560px){ .sj-grid { grid-template-columns:1fr; } }
        .sj-field { display:flex; flex-direction:column; gap:.3rem; font-size:.78rem; font-weight:800; color:#374151; }
        .sj-field input, .sj-field textarea {
            font-family:'Nunito',sans-serif; font-weight:600; font-size:.9rem; color:var(--sj-ink);
            background:#fff; border:1.5px solid var(--sj-line); border-radius:10px; padding:.5rem .7rem;
        }
        .sj-field input:focus, .sj-field textarea:focus { outline:none; border-color:var(--sj-teal); box-shadow:0 0 0 3px rgba(14,116,144,.12); }
        .sj-field-flag input { border-color:#f59e0b; box-shadow:0 0 0 3px rgba(245,158,11,.15); }

        /* timeline */
        .sj-term { font-size:.72rem; font-weight:900; letter-spacing:.08em; text-transform:uppercase; color:#b45309; margin:0 0 .5rem; }
        .sj-row {
            display:flex; align-items:center; justify-content:space-between; gap:.75rem;
            background:#f8fbfe; border:1.5px solid var(--sj-line); border-radius:12px; padding:.6rem .85rem; margin-bottom:.5rem;
        }
        .sj-row-date { font-weight:800; color:var(--sj-ink); font-size:.88rem; }
        .sj-row-meta { color:var(--sj-muted); font-size:.82rem; }
        .sj-score { font-weight:900; color:#0f766e; font-size:.9rem; }
        .sj-badge { font-size:.68rem; font-weight:800; padding:.2rem .6rem; border-radius:999px; background:#f0fdf4; color:#166534; border:1.5px solid #bbf7d0; }
        .sj-empty { color:var(--sj-muted); font-size:.88rem; margin:0; }

        /* per-question */
        .sj-q { display:flex; gap:.9rem; background:#f8fbfe; border:1.5px solid var(--sj-line); border-radius:14px; padding:.75rem; margin-bottom:.7rem; }
        .sj-q-clip { flex:0 0 120px; }
        .sj-q-clip img { width:120px; height:96px; object-fit:cover; border-radius:9px; border:1.5px solid var(--sj-line); display:block; background:#eef4fa; }
        .sj-q-body { min-width:0; font-size:.86rem; }
        .sj-q-head { font-weight:800; color:var(--sj-ink); margin:0 0 .15rem; line-height:1.35; }
        .sj-topic-unmatched { color:#b45309; }
        .sj-chip-ok { color:#16a34a; font-weight:900; }
        .sj-chip-no { color:#e11d48; font-weight:900; }
        .sj-q-prompt { color:var(--sj-muted); margin:.1rem 0 0; }
        .sj-q-ans { color:var(--sj-muted); margin:.3rem 0 0; }
        .sj-q-ans strong { color:var(--sj-ink); }
        .sj-q-ans .right { color:#16a34a; }
        .sj-reason { margin:.5rem 0 0; padding:.5rem .7rem; border-radius:9px; background:#fffbeb; border:1px dashed #fcd34d; color:#92400e; font-size:.82rem; line-height:1.45; }

        /* trend */
        .sj-trend-list { list-style:none; margin:0 0 .8rem; padding:0; }
        .sj-trend-list li { font-size:.86rem; color:#374151; padding:.15rem 0; }
        .sj-trend-list li .dot { color:var(--sj-teal); font-weight:900; }
    </style>

    <div class="sj-head">
        <h1 class="sj-title">🏫 School Journal — {{ $student->name }}</h1>
        <p class="sj-lede">
            Graded classroom papers, kept beside {{ $student->name }}’s voyage. Strong results quietly
            confirm what they already know; wobbly ones gently steer the plan — never a gate, never a
            judgement, and never shown in {{ $student->name }}’s own world.
        </p>
    </div>

    {{-- Upload --}}
    <div class="sj-card">
        <h2 class="sj-h2">📄 File a paper</h2>
        <form wire:submit="savePaper" style="display:flex; flex-direction:column; gap:.7rem; align-items:flex-start;">
            <input type="file" wire:model="paper" accept="image/*,.pdf" class="sj-file">
            @error('paper')<p class="sj-err">{{ $message }}</p>@enderror
            <button type="submit" class="sj-btn" wire:loading.attr="disabled" wire:target="paper">
                <span wire:loading.remove wire:target="paper">File it</span>
                <span wire:loading wire:target="paper">Reading it…</span>
            </button>
        </form>
        @if ($uploadNote)
            <p class="sj-ok-note">{{ $uploadNote }}</p>
        @endif
    </div>

    {{-- Confirm / correct (SJ-02) --}}
    @if ($confirmingId)
        @php($entry = App\Models\SchoolJournalEntry::find($confirmingId))
        <div class="sj-card sj-card-gold">
            <h2 class="sj-h2">✅ Check what was read — then confirm</h2>
            <p class="sj-note">
                Fields the reader was unsure about are outlined in gold. Fix anything wrong — you have the
                final say, always.
            </p>
            <form wire:submit="confirmEntry" style="display:flex; flex-direction:column; gap:.8rem;">
                <div class="sj-grid">
                    @foreach ([
                        'assessment_date' => ['Date of assessment', 'date'],
                        'term' => ['Term (e.g. Term I 2026)', 'text'],
                        'subject' => ['Subject', 'text'],
                        'strand' => ['Strand (e.g. Grammar, Number)', 'text'],
                        'assessment_type' => ['Assessment type (test, essay…)', 'text'],
                        'score' => ['Score as written (e.g. 18/20)', 'text'],
                    ] as $field => [$label, $type])
                        <label class="sj-field @if($entry && $this->lowConfidence($entry, $field)) sj-field-flag @endif" for="form-{{ $field }}">
                            {{ $label }}
                            <input id="form-{{ $field }}" type="{{ $type }}" wire:model="form.{{ $field }}">
                        </label>
                    @endforeach
                </div>
                <label class="sj-field">Teacher’s comment
                    <textarea wire:model="form.teacher_comment" rows="2"></textarea>
                </label>
                @error('form.*')<p class="sj-err">{{ $message }}</p>@enderror
                <div style="display:flex; gap:.6rem;">
                    <button type="submit" class="sj-btn">Confirm entry</button>
                    <button type="button" class="sj-btn sj-btn-ghost" wire:click="$set('confirmingId', null)">Later</button>
                </div>
            </form>
        </div>
    @endif

    {{-- Term timeline (SJ-03) --}}
    <div class="sj-card">
        <h2 class="sj-h2">📘 The journal</h2>
        @forelse ($grouped as $term => $entries)
            <p class="sj-term">{{ $term }}</p>
            <div style="margin-bottom:1rem;">
                @foreach ($entries as $e)
                    <div class="sj-row">
                        <div>
                            <span class="sj-row-date">{{ $e->assessment_date?->format('j M Y') }}</span>
                            <span class="sj-row-meta"> · {{ $e->strand ?? 'strand not set' }} · {{ $e->assessment_type ?? '' }}</span>
                        </div>
                        <div style="display:flex; align-items:center; gap:.5rem;">
                            <span class="sj-score">{{ $e->score ?? '—' }}</span>
                            @if ($e->digitisation_status === App\Models\SchoolJournalEntry::STATUS_CONFIRMED)
                                <span class="sj-badge">confirmed</span>
                            @elseif ($e->digitisation_status === App\Models\SchoolJournalEntry::STATUS_DIGITISED)
                                <button type="button" class="sj-btn sj-btn-sm" wire:click="beginConfirmation({{ $e->id }})">Check it</button>
                            @else
                                <button type="button" class="sj-btn sj-btn-sm sj-btn-ghost" wire:click="beginConfirmation({{ $e->id }})">Enter details</button>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @empty
            <p class="sj-empty">No papers filed yet — the first one goes here.</p>
        @endforelse
    </div>

    {{-- Per-question breakdown (SJ-11..13) — clips, alignment, reasoning --}}
    @php($questionsByEntry = App\Models\SchoolJournalQuestion::whereIn('school_journal_entry_id', $grouped->flatten()->pluck('id'))->with('module')->get()->groupBy('school_journal_entry_id'))
    @foreach ($questionsByEntry as $entryId => $questions)
        <div class="sj-card">
            <h2 class="sj-h2">🧩 Questions — topics tested</h2>
            @foreach ($questions as $q)
                <div class="sj-q">
                    <div class="sj-q-clip">
                        <a href="{{ route('guardian.journal.clip', $q) }}" target="_blank" rel="noopener" title="Open the clip">
                            <img src="{{ route('guardian.journal.clip', $q) }}" alt="Question {{ $q->number }} clip">
                        </a>
                    </div>
                    <div class="sj-q-body">
                        <p class="sj-q-head">
                            Q{{ $q->number ?? '?' }} ·
                            @if ($q->module)
                                {{ $q->module->code }} — {{ $q->module->topic }}
                            @else
                                <span class="sj-topic-unmatched">{{ $q->topic_label ?? 'topic not matched' }}</span>
                                @if ($q->topic_label)<em style="color:var(--sj-muted); font-weight:600;"> (needs confirming)</em>@endif
                            @endif
                            @if ($q->is_correct === true)<span class="sj-chip-ok"> ✓</span>@elseif ($q->is_correct === false)<span class="sj-chip-no"> ✗</span>@endif
                        </p>
                        @if ($q->prompt)<p class="sj-q-prompt">{{ \Illuminate\Support\Str::limit($q->prompt, 120) }}</p>@endif
                        <p class="sj-q-ans">
                            Wrote: <strong>{{ $q->student_answer ?? '—' }}</strong>
                            @if ($q->is_correct === false && $q->correct_answer)
                                · Marked: <strong class="right">{{ $q->correct_answer }}</strong>
                            @endif
                        </p>
                        @if ($q->reasoning_note)
                            <p class="sj-reason">🧠 {{ $q->reasoning_note }}</p>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endforeach

    {{-- Trend (SJ-09) --}}
    @if ($trend !== [])
        <div class="sj-card">
            <h2 class="sj-h2">📈 School picture by term</h2>
            @foreach ($trend as $termBlock)
                <p class="sj-term">{{ $termBlock['term'] }}</p>
                <ul class="sj-trend-list">
                    @foreach ($termBlock['strands'] as $s)
                        <li><span class="dot">•</span> {{ $s['strand'] }} — {{ $s['score'] }} ({{ $s['assessment'] }})</li>
                    @endforeach
                </ul>
            @endforeach
        </div>
    @endif
</div>
