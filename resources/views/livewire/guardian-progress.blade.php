<div>
    <style>
        /* Progress drill-down — same light editorial tokens as the dashboard */
        .pg-head { margin-bottom: 20px; }
        .pg-title { font-family: 'Fredoka','Nunito',sans-serif; font-weight: 600; font-size: 28px; color: var(--ink); margin: 0 0 3px; }
        .pg-sub { font-size: 14px; color: var(--ink-faint); font-weight: 700; margin: 0; }

        .pg-card { background: var(--paper-2); border: 1px solid var(--line); border-radius: var(--radius); padding: 18px 20px; box-shadow: var(--shadow-sm); margin-bottom: 14px; }
        .pg-eyebrow { font-size: 11px; font-weight: 800; letter-spacing: .1em; text-transform: uppercase; color: var(--teal); margin: 0 0 12px; }

        .pg-subject { display: flex; align-items: baseline; justify-content: space-between; gap: 10px; margin: 0 0 4px; }
        .pg-subject h2 { font-family: 'Fredoka','Nunito',sans-serif; font-weight: 600; font-size: 18px; color: var(--ink); }
        .pg-summary { font-size: 12.5px; font-weight: 800; color: var(--ink-soft); font-variant-numeric: tabular-nums; }
        .pg-bar { height: 7px; background: var(--paper); border: 1px solid var(--line); border-radius: 999px; overflow: hidden; margin: 8px 0 16px; }
        .pg-bar i { display: block; height: 100%; background: linear-gradient(90deg, var(--teal), var(--teal-deep)); border-radius: 999px; }

        .pg-bucket { margin-bottom: 14px; padding-left: 12px; border-left: 3px solid var(--line); }
        .pg-bucket:last-child { margin-bottom: 0; }
        .pg-bucket.working_on { border-left-color: var(--amber); }
        .pg-bucket.in_review  { border-left-color: #7cc3cc; }
        .pg-bucket.mastered   { border-left-color: var(--teal); }
        .pg-bucket-name { font-size: 12.5px; font-weight: 800; color: var(--ink); margin: 0 0 6px; display: flex; align-items: baseline; gap: 8px; text-transform: uppercase; letter-spacing: .04em; }
        .pg-bucket-count { font-size: 11.5px; font-weight: 800; color: var(--ink-faint); font-variant-numeric: tabular-nums; }
        .pg-mods { list-style: none; margin: 0; padding: 0; }
        .pg-mod { font-size: 13.5px; color: var(--ink-soft); padding: 5px 0; border-bottom: 1px solid var(--line); }
        .pg-mod:last-child { border-bottom: none; }
        .pg-empty { font-size: 13px; color: var(--ink-faint); font-style: italic; margin: 0; }

        .pg-upcoming { margin-top: 12px; border-top: 1px solid var(--line); padding-top: 12px; }
        .pg-upcoming > summary { cursor: pointer; font-size: 12.5px; font-weight: 800; color: var(--teal); list-style: none; }
        .pg-upcoming > summary::-webkit-details-marker { display: none; }
        .pg-upcoming > summary::before { content: '▸ '; }
        .pg-upcoming[open] > summary::before { content: '▾ '; }
        .pg-unassessed { font-size: 13.5px; color: var(--ink-faint); font-style: italic; margin: 0; }
    </style>

    @unless ($hasChild)
        {{-- No child yet: mirror the dashboard's Add child empty state (GO-18). --}}
        <div style="text-align:center; padding:40px 24px; background:var(--paper-2,#fff); border:1px solid var(--line,#e6e6e6); border-radius:16px; box-shadow:0 1px 3px rgba(0,0,0,.05);">
            <p style="font-size:44px; margin-bottom:8px;">👧</p>
            <h2 style="margin-bottom:8px; font-weight:800;">Add your first child</h2>
            <p style="max-width:440px; margin:0 auto 20px; color:var(--ink-faint,#667);">
                Set up your child's profile to see their progress here. It only takes a minute.
            </p>
            <a href="{{ route('child.setup') }}"
               style="display:inline-block; text-decoration:none; padding:12px 24px; font-size:15px; font-weight:800; color:#5a3d00; background:var(--amber,#f6b71e); border-radius:11px;">
                ➕ Add child
            </a>
        </div>
    @else

    <div class="pg-head">
        <h1 class="pg-title">Progress drill-down</h1>
        <p class="pg-sub">What needs attention first — the whole syllabus is here too.</p>
    </div>

    @php
        // GD-08: lead with the buckets a guardian can act on; upcoming comes last, collapsed.
        $actionableLabels = [
            'working_on' => 'Working on',
            'in_review'  => 'In review',
            'mastered'   => 'Mastered',
        ];
    @endphp

    @foreach ($buckets as $subject => $subjectBuckets)
        <div class="pg-card">
            <div class="pg-subject">
                <h2>{{ $subject === 'Math' ? 'Mathematics' : $subject }}</h2>
                <span class="pg-summary">{{ $summaries[$subject]['mastered'] }} of {{ $summaries[$subject]['total'] }} mastered</span>
            </div>
            <div class="pg-bar"><i style="width: {{ $summaries[$subject]['total'] > 0 ? (int) round(($summaries[$subject]['mastered'] / $summaries[$subject]['total']) * 100) : 0 }}%;"></i></div>

            @foreach ($actionableLabels as $key => $label)
                <div class="pg-bucket {{ $key }}">
                    <p class="pg-bucket-name">{{ $label }} <span class="pg-bucket-count">{{ count($subjectBuckets[$key]) }}</span></p>
                    @if (count($subjectBuckets[$key]) > 0)
                        <ul class="pg-mods">
                            @foreach ($subjectBuckets[$key] as $module)
                                <li class="pg-mod">{{ $module['topic'] }}</li>
                            @endforeach
                        </ul>
                    @else
                        <p class="pg-empty">None here yet.</p>
                    @endif
                </div>
            @endforeach

            {{-- Upcoming: the long tail, collapsed so it never buries the actionable buckets --}}
            @if (count($subjectBuckets['upcoming']) > 0)
                <details class="pg-upcoming">
                    <summary>Upcoming ({{ count($subjectBuckets['upcoming']) }}) — Show all</summary>
                    <ul class="pg-mods" style="margin-top:8px;">
                        @foreach ($subjectBuckets['upcoming'] as $module)
                            <li class="pg-mod">{{ $module['topic'] }}</li>
                        @endforeach
                    </ul>
                </details>
            @endif
        </div>
    @endforeach

    {{-- Writing is a parallel track (WR-01–05): no module buckets, honest awaiting state. --}}
    <div class="pg-card">
        <p class="pg-eyebrow">Writing</p>
        <p class="pg-unassessed">Writing is a paper awaiting its own assessment track.</p>
    </div>
    @endunless
</div>
