<div>
    @if ($open)
        <style>
            .fpw { border:1px solid var(--line); border-radius:16px; background:var(--paper-2); padding:18px 20px; box-shadow:var(--shadow-sm); margin-bottom:16px; }
            .fpw-eyebrow { font-size:10.5px; font-weight:800; letter-spacing:.12em; text-transform:uppercase; color:var(--teal); margin:0; }
            .fpw-h { font-size:18px; font-weight:800; color:var(--ink); margin:5px 0 4px; }
            .fpw-sub { font-size:13px; color:var(--ink-soft); margin:0 0 16px; line-height:1.5; }
            .fpw-label { display:block; font-size:12px; font-weight:800; color:var(--ink-faint); margin:0 0 6px; }
            .fpw-input { width:100%; padding:11px 12px; border:1.5px solid var(--line); border-radius:11px; font-size:15px; color:var(--ink); background:var(--paper); }
            .fpw-input:focus { outline:none; border-color:var(--teal); }
            .fpw-hint { font-size:11.5px; color:var(--ink-soft); margin:5px 0 0; }
            .fpw-err { font-size:12px; color:#b91c1c; font-weight:700; margin:5px 0 0; }
            .fpw-group { margin-top:16px; }
            .fpw-group-h { font-size:12.5px; font-weight:800; color:var(--ink); margin:0 0 8px; }
            .fpw-chips { display:flex; flex-wrap:wrap; gap:7px; }
            .fpw-chip { display:inline-flex; align-items:center; gap:6px; font-size:12.5px; font-weight:700; color:var(--ink-soft); background:var(--paper); border:1px solid var(--line); border-radius:999px; padding:7px 12px; cursor:pointer; }
            .fpw-chip input { accent-color:var(--teal); }
            .fpw-acc { border:1px solid var(--line); border-radius:11px; padding:0; margin-top:8px; background:var(--paper); }
            .fpw-acc[open] { padding-bottom:10px; }
            .fpw-acc-sum { cursor:pointer; list-style:none; padding:11px 13px; font-size:13px; font-weight:800; color:var(--ink); display:flex; justify-content:space-between; align-items:center; gap:8px; }
            .fpw-acc-sum::-webkit-details-marker { display:none; }
            .fpw-acc-sum::after { content:"▸"; color:var(--ink-faint); font-size:12px; }
            .fpw-acc[open] > .fpw-acc-sum::after { content:"▾"; }
            .fpw-acc-sum span { font-size:11px; font-weight:700; color:var(--ink-faint); }
            .fpw-acc-sub { margin:0 13px; }
            .fpw-actions { display:flex; gap:10px; margin-top:18px; }
            .fpw-save { flex:1; text-align:center; background:var(--teal); color:#fff; border:0; border-radius:11px; padding:12px; font-size:14px; font-weight:800; cursor:pointer; }
            .fpw-skip { background:none; border:0; color:var(--ink-faint); font-size:13px; font-weight:800; cursor:pointer; padding:12px; }
        </style>

        <section class="fpw" aria-label="Finish your profile">
            <p class="fpw-eyebrow">Optional · finish your profile</p>
            <h2 class="fpw-h">A couple of quick extras</h2>
            <p class="fpw-sub">Both logins are ready — this just helps us tailor things. You can skip it and do it anytime.</p>

            <div style="margin-bottom:14px;">
                <label class="fpw-label" for="fpw-name">Your name (parent / guardian)</label>
                <input type="text" id="fpw-name" class="fpw-input" wire:model="name" placeholder="e.g. Maria Thomas" autocomplete="name">
                @error('name') <p class="fpw-err">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="fpw-label" for="fpw-phone">Your mobile (WhatsApp) — optional</label>
                <input type="tel" id="fpw-phone" class="fpw-input" wire:model="phone" placeholder="+1 868 555 1234" autocomplete="tel">
                <p class="fpw-hint">Full international format. We'll only use it for account updates.</p>
                @error('phone') <p class="fpw-err">{{ $message }}</p> @enderror
            </div>

            @php ($strands = $this->strandsBySubject())
            @if (! empty($strands))
                <details class="fpw-acc" style="margin-top:16px;">
                    <summary class="fpw-acc-sum">Areas your child finds tricky? <span>optional — tap to add</span></summary>
                    <p class="fpw-hint" style="margin:8px 0 4px;">Leave this closed if nothing stands out — the diagnostic finds the rest, and you can add these anytime.</p>
                    @foreach ($strands as $subject => $subjectStrands)
                        <details class="fpw-acc fpw-acc-sub">
                            <summary class="fpw-acc-sum">{{ $subject }}</summary>
                            <div class="fpw-chips" style="margin-top:8px;">
                                @foreach ($subjectStrands as $strand)
                                    <label class="fpw-chip">
                                        <input type="checkbox" wire:model="weakAreas" value="{{ $strand }}">
                                        {{ $strand }}
                                    </label>
                                @endforeach
                            </div>
                        </details>
                    @endforeach
                </details>
            @endif

            {{-- Primary actions stay directly in view — the checklist above is collapsed so it never pushes them below the fold. --}}
            <div class="fpw-actions">
                <button type="button" class="fpw-save" wire:click="save">Save</button>
                <button type="button" class="fpw-skip" wire:click="skip">Skip for now</button>
            </div>
        </section>
    @endif
</div>
