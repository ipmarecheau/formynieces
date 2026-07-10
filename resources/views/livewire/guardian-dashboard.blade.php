<div class="mx-auto max-w-3xl space-y-6 p-6">
    <header class="border-b pb-4">
        <h1 class="text-xl font-semibold text-slate-800">Weekly guardian summary</h1>
        <p class="text-sm text-slate-500">The four questions, answered honestly.</p>
    </header>

    {{-- Q1 --}}
    <section class="rounded-lg border p-4">
        <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-600">This week's target</h2>
        <p class="mt-1 text-slate-800">
            @if ($targetCompleted)
                Completed — every module for this week is done.
            @else
                Not yet complete this week.
            @endif
        </p>
    </section>

    {{-- Q2 --}}
    <section class="rounded-lg border p-4">
        <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-600">Pace</h2>

        @if ($paceStatus === 'warning')
            <p class="mt-1 text-sm text-slate-700">
                {{ $weeksBehind }} week(s) behind the pacing calendar.
            </p>
        @endif

        <ul class="mt-2 space-y-1">
            @foreach ($pace as $paper => $row)
                <li class="flex items-center justify-between text-sm text-slate-700">
                    <span>{{ $paper }} <span class="text-slate-400">({{ $row['weight'] }}%)</span></span>
                    <span>
                        @if ($row['assessed'])
                            {{ $row['completed'] }}/{{ $row['expected'] }} on track,
                            {{ $row['behind_count'] }} behind
                        @else
                            Not yet assessed
                        @endif
                    </span>
                </li>
            @endforeach
        </ul>
    </section>

    {{-- Q3 --}}
    <section class="rounded-lg border p-4">
        <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-600">Recommendation</h2>
        <p class="mt-1 text-slate-800">{{ $recommendation }}</p>
    </section>

    {{-- Q4 --}}
    <section class="rounded-lg border p-4">
        <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-600">Writing feedback</h2>
        <p class="mt-1 text-slate-800">
            @if ($writingFeedback)
                Latest feedback available.
            @else
                No writing feedback yet.
            @endif
        </p>
    </section>
</div>
