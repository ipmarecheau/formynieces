<div>
    <style>
        .fm-head { margin-bottom: 22px; }
        .fm-h1 { font-family:'Fredoka','Nunito',sans-serif; font-weight:600; font-size:30px; color:var(--ink); margin:0 0 3px; }
        .fm-sub { font-size:14px; color:var(--ink-faint); font-weight:700; margin:0; }

        .fm-card { background:var(--paper-2); border:1px solid var(--line); border-radius:var(--radius); padding:20px 22px; box-shadow:var(--shadow-sm); margin-bottom:16px; }
        .fm-card h2 { font-family:'Fredoka','Nunito',sans-serif; font-weight:600; font-size:19px; color:var(--ink); margin:0 0 4px; }
        .fm-note { font-size:13px; color:var(--ink-faint); margin:0 0 16px; }

        .fm-child { border:1px solid var(--line); border-radius:12px; padding:16px; margin-bottom:14px; background:var(--paper); }
        .fm-child:last-child { margin-bottom:0; }
        .fm-grid { display:grid; grid-template-columns:1fr 1fr; gap:12px; }
        @media (max-width:640px){ .fm-grid{ grid-template-columns:1fr; } }

        .fm-field label { display:block; font-size:12px; font-weight:800; letter-spacing:.04em; text-transform:uppercase; color:var(--ink-faint); margin-bottom:6px; }
        .fm-field .opt { color:var(--ink-faint); font-weight:700; text-transform:none; letter-spacing:0; }
        .fm-field input { width:100%; font-family:'Nunito',sans-serif; font-size:15px; color:var(--ink); background:var(--paper-2); border:1px solid var(--line); border-radius:11px; padding:10px 13px; }
        .fm-field input:focus { outline:none; border-color:var(--teal); box-shadow:0 0 0 3px var(--teal-tint); }
        .fm-err { color:var(--warn,#c0392b); font-size:12.5px; font-weight:700; margin:6px 0 0; }

        .fm-btn { font-family:'Nunito',sans-serif; font-size:14px; font-weight:800; cursor:pointer; color:#5a3d00; background:var(--amber); border:none; border-radius:11px; padding:10px 18px; margin-top:12px; }
        .fm-btn:hover { filter:brightness(1.05); }
        .fm-btn-out { color:var(--teal-deep); background:var(--paper-2); border:1px solid var(--line); }
        .fm-btn-danger { color:#fff; background:var(--warn,#c0392b); padding:7px 12px; font-size:12.5px; margin:0; }
        .fm-flash { background:var(--good-tint,#e6f6ee); border:1px solid var(--good,#2e9e6b); color:var(--good,#1f7a51); border-radius:11px; padding:12px 16px; margin-bottom:16px; font-weight:700; font-size:14px; }

        .fm-cp { display:flex; align-items:center; justify-content:space-between; gap:12px; padding:12px 0; border-bottom:1px solid var(--line); }
        .fm-cp:last-child { border-bottom:0; }
        .fm-cp .who { font-size:14px; }
        .fm-cp .who b { color:var(--ink); }
        .fm-cp .who span { color:var(--ink-faint); }
        .fm-badge { display:inline-flex; align-items:center; gap:5px; padding:3px 9px; border-radius:999px; font-size:11.5px; font-weight:800; background:var(--amber-tint,#fdf0d5); color:#8a5a00; }
        .fm-empty { font-size:14px; color:var(--ink-faint); font-style:italic; }
    </style>

    <div class="fm-head">
        <h1 class="fm-h1">Family</h1>
        <p class="fm-sub">Your children's details and the other parent.</p>
    </div>

    @if ($flash)
        <div class="fm-flash" role="status">{{ $flash }}</div>
    @endif

    {{-- Children --}}
    <div class="fm-card">
        <h2>Children</h2>
        <p class="fm-note">Only the name is required — birth year and school are optional and can be added anytime.</p>

        @forelse ($students as $child)
            <div class="fm-child" wire:key="child-{{ $child->id }}">
                <div class="fm-grid">
                    <div class="fm-field">
                        <label>Name</label>
                        <input id="child-{{ $child->id }}-name" type="text" wire:model="children.{{ $child->id }}.name">
                        @error("children.{$child->id}.name") <p class="fm-err">{{ $message }}</p> @enderror
                    </div>
                    <div class="fm-field">
                        <label>Birth year <span class="opt">(optional)</span></label>
                        <input id="child-{{ $child->id }}-birth_year" type="number" min="2005" max="{{ now()->year }}" placeholder="e.g. 2016"
                               wire:model="children.{{ $child->id }}.birth_year">
                        @error("children.{$child->id}.birth_year") <p class="fm-err">{{ $message }}</p> @enderror
                    </div>
                    <div class="fm-field">
                        <label>Current school <span class="opt">(optional)</span></label>
                        <input id="child-{{ $child->id }}-current_school" type="text" placeholder="e.g. St. Mary&#39;s Government"
                               wire:model="children.{{ $child->id }}.current_school">
                        @error("children.{$child->id}.current_school") <p class="fm-err">{{ $message }}</p> @enderror
                    </div>
                    <div class="fm-field">
                        <label>Target SEA year <span class="opt">(optional)</span></label>
                        <input type="number" min="2025" max="2035" placeholder="e.g. 2027"
                               wire:model="children.{{ $child->id }}.target_sea_year">
                        @error("children.{$child->id}.target_sea_year") <p class="fm-err">{{ $message }}</p> @enderror
                    </div>
                </div>
                <button type="button" class="fm-btn" wire:click="saveChild({{ $child->id }})">Save {{ $child->name }}</button>
            </div>
        @empty
            <p class="fm-empty">No children yet.</p>
        @endforelse

        <a href="{{ route('child.setup') }}" wire:navigate class="fm-btn fm-btn-out" style="display:inline-block; text-decoration:none;">➕ Add another child</a>
    </div>

    {{-- Co-parent --}}
    <div class="fm-card">
        <h2>The other parent</h2>
        <p class="fm-note">Invite your child's other parent or guardian. They'll get an email to join with this address.</p>

        @if ($coParents->isNotEmpty())
            <div style="margin-bottom:16px;">
                @foreach ($coParents as $coParent)
                    <div class="fm-cp" wire:key="cp-{{ $coParent->id }}">
                        <div class="who">
                            <b>{{ $coParent->name }}</b> <span>· {{ $coParent->email }}</span>
                            @if ($coParent->relationship)<span>· {{ $coParent->relationship }}</span>@endif
                            <div style="margin-top:4px;"><span class="fm-badge">{{ ucfirst($coParent->status) }}</span></div>
                        </div>
                        <button type="button" class="fm-btn fm-btn-danger"
                                wire:click="removeCoParent({{ $coParent->id }})"
                                wire:confirm="Remove {{ $coParent->name }} as a co-parent?">Remove</button>
                    </div>
                @endforeach
            </div>
        @endif

        <form wire:submit="addCoParent">
            <div class="fm-grid">
                <div class="fm-field">
                    <label>Their name</label>
                    <input id="co-name" type="text" wire:model="coName" placeholder="e.g. Marcus Thomas">
                    @error('coName') <p class="fm-err">{{ $message }}</p> @enderror
                </div>
                <div class="fm-field">
                    <label>Their email</label>
                    <input id="co-email" type="email" wire:model="coEmail" placeholder="them@example.com">
                    @error('coEmail') <p class="fm-err">{{ $message }}</p> @enderror
                </div>
                <div class="fm-field">
                    <label>Relationship <span class="opt">(optional)</span></label>
                    <input id="co-relationship" type="text" wire:model="coRelationship" placeholder="e.g. Father, Aunt">
                    @error('coRelationship') <p class="fm-err">{{ $message }}</p> @enderror
                </div>
            </div>
            <button type="submit" class="fm-btn">Send invitation</button>
        </form>
    </div>
</div>
