<div>
    <style>
        .rr-eyebrow { font-size: 12px; font-weight: 800; letter-spacing: .16em; text-transform: uppercase; color: var(--teal); margin: 0 0 8px; }
        .rr-band { font-family: 'Fredoka', sans-serif; font-weight: 600; font-size: clamp(22px, 4vw, 30px); line-height: 1.15; margin: 0 0 6px; }
        .rr-score { font-size: 14px; color: var(--ink-soft); margin: 0 0 20px; }
        .rr-card { background: linear-gradient(155deg, var(--teal), var(--teal-deep)); color: #fff; border-radius: 18px; padding: 22px; margin: 0 0 22px; }
        .rr-card .rr-c-l { font-size: 11px; font-weight: 800; letter-spacing: .14em; text-transform: uppercase; color: rgba(255,255,255,.75); margin: 0 0 6px; }
        .rr-card .rr-c-v { font-family: 'Fredoka', sans-serif; font-weight: 700; font-size: 40px; line-height: 1; margin: 0; }
        .rr-card .rr-c-s { font-size: 13.5px; color: rgba(255,255,255,.85); margin: 8px 0 0; }
        .rr-sec { font-size: 12px; font-weight: 800; letter-spacing: .06em; text-transform: uppercase; color: var(--ink-soft); margin: 0 0 8px; }
        .rr-strands { list-style: none; margin: 0 0 20px; padding: 0; display: grid; gap: 7px; }
        .rr-strands li { font-size: 15px; font-weight: 700; color: var(--ink); padding-left: 24px; position: relative; }
        .rr-strands li::before { content: '⚑'; position: absolute; left: 0; color: var(--amber); }
        .rr-next { background: var(--amber-tint); border: 1px solid #f2d69a; border-radius: 12px; padding: 14px 16px; font-size: 15px; font-weight: 700; color: #5a3d00; margin: 0 0 24px; }
        .rr-cta { display: block; text-align: center; background: linear-gradient(160deg, #ffd15c, #f2941f); color: #241505; font-family: 'Fredoka', sans-serif; font-weight: 600; font-size: 17px; border: 0; border-radius: 12px; padding: 15px; cursor: pointer; text-decoration: none; box-shadow: 0 10px 24px rgba(242,169,0,.3); }
        .rr-share { display: inline-flex; align-items: center; gap: 8px; margin-top: 14px; background: none; border: 1px solid var(--line); border-radius: 999px; padding: 9px 16px; font-family: inherit; font-weight: 800; font-size: 13.5px; color: var(--teal-deep); cursor: pointer; }
        .rr-mailed { font-size: 12.5px; color: var(--ink-soft); text-align: center; margin: 14px 0 0; }
    </style>

    @if ($lead)
        <p class="rr-eyebrow">Your child's placement report</p>
        <h2 class="rr-band">{{ $lead->placement_band }}</h2>
        <p class="rr-score">Projected first-choice readiness{{ $lead->mock_score !== null ? ' · scored '.$lead->mock_score.'% on the mock' : '' }}.</p>

        {{-- Shareable SEA-Ready card (LG-06) --}}
        <div class="rr-card" id="rr-card">
            <p class="rr-c-l">🇹🇹 SEA-Ready score</p>
            <p class="rr-c-v">{{ $lead->mock_score ?? '—' }}%</p>
            <p class="rr-c-s">{{ $lead->placement_band }} — measured on a free SmoothSeas SEA mock.</p>
        </div>

        @if (! empty($lead->weakest_strands))
            <p class="rr-sec">The three strands to fix first</p>
            <ul class="rr-strands">
                @foreach ($lead->weakest_strands as $strand)
                    <li>{{ $strand }}</li>
                @endforeach
            </ul>
        @endif

        <p class="rr-sec">Your one next step</p>
        <p class="rr-next">{{ $lead->next_step }}</p>

        <button type="button" class="rr-cta" wire:click="claimTrial" wire:loading.attr="disabled">
            <span wire:loading.remove wire:target="claimTrial">Start your free month + get the practice pack →</span>
            <span wire:loading wire:target="claimTrial">Starting your free month…</span>
        </button>

        <div style="text-align:center;">
            <button type="button" class="rr-share"
                    onclick="navigator.share ? navigator.share({title:'My child is SEA-Ready',text:'My child scored {{ $lead->mock_score }}% on a free SEA mock — {{ $lead->placement_band }}.',url:'{{ route('placement-report') }}'}) : (navigator.clipboard.writeText('{{ route('placement-report') }}'), this.textContent='Link copied!')">
                📤 Share our SEA-Ready score
            </button>
        </div>

        <label style="display:flex; gap:9px; align-items:flex-start; margin-top:18px; font-size:13.5px; color:var(--ink-soft); cursor:pointer;">
            <input type="checkbox" wire:model.live="weeklyOptIn" style="margin-top:3px;">
            <span>Email me a free <strong>SEA Question of the Week</strong> with a worked solution — until SEA 2027.</span>
        </label>

        <p class="rr-mailed">📧 We've emailed this report to {{ $lead->email }}.</p>
    @endif
</div>
