@component('mail::message')
# 🔄 Worth another look

Hi {{ $name ?: 'there' }},

You tested **{{ $scenarioTitle }}** earlier (you said
“{{ ['worked' => 'It worked', 'issue' => 'Something was off', 'skipped' => 'Skipped'][$lastResult] ?? $lastResult }}”).
It has changed since, so your answer may be out of date.

**What changed**
@if ($specChanged && $implChanged)
- The **steps** for this scenario were updated, and
- the **feature itself** was changed.
@elseif ($specChanged)
- The **steps** for this scenario were updated.
@else
- The **feature itself** was changed.
@endif

@if (! empty($commits))
Recent updates to this part of the app:
@foreach ($commits as $commit)
- {{ $commit }}
@endforeach
@endif

@component('mail::button', ['url' => $retestUrl])
Re-test this now
@endcomponent

It only takes a minute — your fresh answer replaces the old one.

Thanks for keeping an eye on it,
The SmoothSeas team
@endcomponent
