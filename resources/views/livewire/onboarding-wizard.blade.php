<div>
    @if ($show)
    <style>
        .ow{--ow-teal:#0e7c86;--ow-teal-deep:#0a5a62;--ow-tint:#e4eeed;--ow-ink:#0b2a31;--ow-soft:#43616a;--ow-faint:#6e8890;--ow-line:#d2e0dc;--ow-amber:#b06f10;--ow-amber-bg:#f7ecd4;
            border:1px solid var(--ow-line);border-radius:18px;background:#fff;padding:22px 24px;box-shadow:0 1px 2px rgba(11,42,49,.04),0 14px 34px -20px rgba(11,42,49,.22);font-family:'Nunito',system-ui,sans-serif;color:var(--ow-ink)}
        .ow.done{background:linear-gradient(180deg,#fff,var(--ow-tint))}
        .ow-top{display:flex;align-items:flex-start;justify-content:space-between;gap:16px}
        .ow-eyebrow{font-size:11px;font-weight:800;letter-spacing:.12em;text-transform:uppercase;color:var(--ow-teal);margin:0}
        .ow-h{font-size:18px;font-weight:800;margin:5px 0 0}
        .ow-hide{border:0;background:none;color:var(--ow-faint);font-size:13px;font-weight:700;cursor:pointer;padding:4px 6px;border-radius:8px}
        .ow-hide:hover{color:var(--ow-ink)}
        .ow-prog{display:flex;justify-content:space-between;font-size:12px;font-weight:800;color:var(--ow-faint);margin:16px 0 5px}
        .ow-bar{height:8px;border-radius:999px;background:#eef2f1;overflow:hidden}
        .ow-bar > span{display:block;height:100%;border-radius:999px;background:var(--ow-teal);transition:width .3s ease}
        .ow-list{list-style:none;margin:18px 0 0;padding:0;display:flex;flex-direction:column;gap:8px}
        .ow-step{display:flex;gap:12px;align-items:flex-start;border:1px solid transparent;border-radius:13px;padding:11px 12px}
        .ow-step.is-done{background:rgba(228,238,237,.5)}
        .ow-step.is-next{border-color:#a9d5d6;background:#fff;box-shadow:0 0 0 3px rgba(14,124,134,.08)}
        .ow-check{flex:none;width:20px;height:20px;border-radius:50%;display:grid;place-items:center;font-size:12px;font-weight:900;border:1px solid var(--ow-line);color:var(--ow-faint)}
        .ow-check.on{background:var(--ow-teal);border-color:var(--ow-teal);color:#fff}
        .ow-label{font-weight:800}
        .ow-step.is-done .ow-label{color:var(--ow-faint);text-decoration:line-through}
        .ow-tag{margin-left:8px;font-size:10px;font-weight:800;letter-spacing:.06em;text-transform:uppercase;color:var(--ow-amber);background:var(--ow-amber-bg);border-radius:999px;padding:2px 8px}
        .ow-why{margin:3px 0 0;font-size:13.5px;color:var(--ow-soft)}
        .ow-cta{margin-top:9px;display:inline-flex;align-items:center;gap:6px;background:var(--ow-teal);color:#fff;font-weight:800;font-size:13.5px;text-decoration:none;border-radius:10px;padding:8px 14px}
        .ow-cta:hover{background:var(--ow-teal-deep)}
        .ow-pill{display:inline-flex;align-items:center;gap:8px;border:1px solid #a9d5d6;background:#fff;border-radius:999px;padding:8px 15px;font-size:14px;font-weight:800;color:var(--ow-teal-deep);cursor:pointer;box-shadow:0 1px 2px rgba(11,42,49,.05)}
        .ow-pill:hover{background:var(--ow-tint)}
        .ow-done-row{display:flex;align-items:flex-start;gap:12px}
        .ow-done-row .em{font-size:26px;line-height:1}
    </style>

    @if ($minimised)
        <button type="button" class="ow-pill" wire:click="reopen">
            <span>🧭</span><span>Getting started · {{ $progress['done'] }}/{{ $progress['total'] }}</span>
        </button>
    @elseif ($complete)
        <section class="ow done">
            <div class="ow-done-row">
                <span class="em">🎉</span>
                <div>
                    <h2 class="ow-h">Your family is all set up!</h2>
                    <p class="ow-why">Everything's ready — she can practise, learn and grow from here. Reopen this anytime from your dashboard.</p>
                </div>
            </div>
        </section>
    @else
        <section class="ow" aria-label="Getting started">
            <div class="ow-top">
                <div>
                    <p class="ow-eyebrow">Getting started</p>
                    <h2 class="ow-h">Let's set {{ $steps[1]['done'] ? 'her' : 'your child' }} up for success</h2>
                </div>
                <button type="button" class="ow-hide" wire:click="minimise" aria-label="Minimise getting started">Hide</button>
            </div>

            <div class="ow-prog"><span>{{ $progress['done'] }} of {{ $progress['total'] }} done</span><span>{{ $progress['percent'] }}%</span></div>
            <div class="ow-bar"><span style="width: {{ $progress['percent'] }}%"></span></div>

            <ol class="ow-list">
                @foreach ($steps as $step)
                    @php $isNext = $next && $step['key'] === $next['key']; @endphp
                    <li class="ow-step {{ $step['done'] ? 'is-done' : ($isNext ? 'is-next' : '') }}">
                        <span class="ow-check {{ $step['done'] ? 'on' : '' }}">{{ $step['done'] ? '✓' : '' }}</span>
                        <div style="min-width:0;flex:1">
                            <span class="ow-label">{{ $step['label'] }}</span>
                            @if ($step['actor'] === 'child' && ! $step['done'])
                                <span class="ow-tag">Her turn</span>
                            @endif
                            @if ($isNext)
                                <p class="ow-why">{{ $step['why'] }}</p>
                                @if ($step['route'])
                                    <a class="ow-cta" href="{{ route($step['route']) }}">{{ $step['label'] }} →</a>
                                @endif
                            @endif
                        </div>
                    </li>
                @endforeach
            </ol>
        </section>
    @endif
    @endif
</div>
