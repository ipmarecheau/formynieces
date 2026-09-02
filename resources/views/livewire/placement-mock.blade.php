<div>
    <style>
        .pm-prog { height: 6px; border-radius: 999px; background: var(--line); overflow: hidden; margin-bottom: 18px; }
        .pm-prog i { display: block; height: 100%; background: var(--teal); border-radius: 999px; transition: width .3s ease; }
        .pm-count { font-size: 12px; font-weight: 800; letter-spacing: .04em; text-transform: uppercase; color: var(--ink-soft); margin: 0 0 10px; }
        .pm-q { font-family: 'Fredoka', sans-serif; font-weight: 600; font-size: 20px; line-height: 1.3; margin: 0 0 18px; }
        .pm-opt { display: block; width: 100%; text-align: left; font-family: inherit; font-size: 15px; font-weight: 700; color: var(--ink); background: var(--paper); border: 1.5px solid var(--line); border-radius: 12px; padding: 14px 16px; margin-bottom: 10px; cursor: pointer; }
        .pm-opt:hover { border-color: var(--teal); }
    </style>

    @php $q = $questions[$index] ?? null; $total = count($questions); @endphp

    @if ($q)
        <div class="pm-prog"><i style="width: {{ $total ? round(($index) / $total * 100) : 0 }}%"></i></div>
        <p class="pm-count">Question {{ $index + 1 }} of {{ $total }}</p>
        <h3 class="pm-q">{{ $q['prompt'] }}</h3>
        @foreach ($q['options'] as $i => $option)
            <button type="button" class="pm-opt" wire:click="answer({{ $i }})" wire:loading.attr="disabled">{{ $option }}</button>
        @endforeach
    @else
        <p class="pm-q">Grading your child's mock…</p>
    @endif
</div>
