<x-mail::message>
# Your SEA Question of the Week

**{{ $daysToSea }} days to SEA 2027.** Here's one to try with your child.

**{{ strip_tags((string) $question->prompt) }}**

@php $options = array_values($question->options ?? []); $correct = (int) $question->correct_index; @endphp
@foreach ($options as $i => $opt)
- {{ chr(65 + $i) }}) {{ $opt }}
@endforeach

<x-mail::panel>
**Answer:** {{ chr(65 + $correct) }}) {{ $options[$correct] ?? '' }}@if ($question->explanation) — {{ strip_tags((string) $question->explanation) }}@endif
</x-mail::panel>

Want a full plan around exactly what your child needs? Start free.

<x-mail::button :url="route('placement-report')">
Start your free month
</x-mail::button>

Smooth 🐢<br>
{{ config('app.name') }}
</x-mail::message>
