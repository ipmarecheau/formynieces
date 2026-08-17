<div>
    <style>
        .gd-header { margin-bottom: 1.5rem; }
        .gd-title {
            font-family: 'Fredoka One', cursive;
            font-size: 1.5rem; color: #0e7490; margin: 0 0 0.25rem;
        }
        .gd-subtitle { font-size: 0.9rem; color: #9ca3af; margin: 0; font-weight: 700; }
        .gd-card {
            background: white; border: 1.5px solid #e6f2fb;
            border-radius: 18px; padding: 1.1rem 1.25rem; margin-bottom: 1rem;
        }
        .gd-eyebrow {
            font-size: 0.68rem; font-weight: 800; color: #93b2cc;
            text-transform: uppercase; letter-spacing: 0.07em; margin: 0 0 0.6rem;
        }
        .gd-subject {
            font-family: 'Fredoka One', cursive; font-size: 1.1rem; color: #0e7490; margin: 0 0 0.9rem;
            display: flex; align-items: baseline; justify-content: space-between; gap: 8px;
        }
        .gd-summary { font-family: 'Nunito', sans-serif; font-size: 0.78rem; font-weight: 700; color: #16a34a; }
        .gd-upcoming { margin-top: 0.7rem; border-top: 1px solid #f1f5f9; padding-top: 0.6rem; }
        .gd-upcoming > summary {
            cursor: pointer; font-size: 0.8rem; font-weight: 800; color: #93b2cc; list-style: none;
        }
        .gd-upcoming > summary::-webkit-details-marker { display: none; }
        .gd-upcoming > summary::before { content: '▸ '; }
        .gd-upcoming[open] > summary::before { content: '▾ '; }
        .gd-bucket { margin-bottom: 0.9rem; }
        .gd-bucket:last-child { margin-bottom: 0; }
        .gd-bucket-name {
            font-size: 0.8rem; font-weight: 800; color: #374151;
            margin: 0 0 0.35rem; display: flex; align-items: baseline; gap: 8px;
        }
        .gd-bucket-count { font-size: 0.72rem; font-weight: 700; color: #93b2cc; }
        .gd-mod-list { list-style: none; margin: 0; padding: 0; }
        .gd-mod {
            font-size: 0.82rem; color: #4b5563; padding: 3px 0;
            border-bottom: 1px solid #f9f5ff;
        }
        .gd-mod:last-child { border-bottom: none; }
        .gd-empty { font-size: 0.78rem; color: #9ca3af; font-style: italic; }
        .gd-unassessed { font-size: 0.82rem; color: #9ca3af; font-weight: 600; font-style: italic; }
    </style>

    <div class="gd-header">
        <h1 class="gd-title">Progress drill-down</h1>
        <p class="gd-subtitle">What needs attention first — the whole syllabus is here too.</p>
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
        <div class="gd-card">
            <p class="gd-subject">
                {{ $subject }}
                <span class="gd-summary">{{ $summaries[$subject]['mastered'] }} of {{ $summaries[$subject]['total'] }} mastered</span>
            </p>

            @foreach ($actionableLabels as $key => $label)
                <div class="gd-bucket">
                    <p class="gd-bucket-name">
                        {{ $label }}
                        <span class="gd-bucket-count">{{ count($subjectBuckets[$key]) }}</span>
                    </p>

                    @if (count($subjectBuckets[$key]) > 0)
                        <ul class="gd-mod-list">
                            @foreach ($subjectBuckets[$key] as $module)
                                <li class="gd-mod">{{ $module['topic'] }}</li>
                            @endforeach
                        </ul>
                    @else
                        <p class="gd-empty">None here yet.</p>
                    @endif
                </div>
            @endforeach

            {{-- Upcoming: the long tail, collapsed so it never buries the actionable buckets --}}
            @if (count($subjectBuckets['upcoming']) > 0)
                <details class="gd-upcoming">
                    <summary>Upcoming ({{ count($subjectBuckets['upcoming']) }}) — Show all</summary>
                    <ul class="gd-mod-list">
                        @foreach ($subjectBuckets['upcoming'] as $module)
                            <li class="gd-mod">{{ $module['topic'] }}</li>
                        @endforeach
                    </ul>
                </details>
            @endif
        </div>
    @endforeach

    {{-- Writing is a parallel track (WR-01–05): no module buckets, honest awaiting state. --}}
    <div class="gd-card">
        <p class="gd-eyebrow">Writing</p>
        <p class="gd-unassessed">Writing is a paper awaiting its own assessment track.</p>
    </div>
</div>
