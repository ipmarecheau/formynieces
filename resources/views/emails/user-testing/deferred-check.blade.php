@component('mail::message')
# {{ $phase === 'before' ? '👀 Time to watch your device' : '✅ Did it arrive?' }}

Hi {{ $name ?: 'there' }},

@if ($phase === 'before')
You asked us to remind you about **{{ $scenarioTitle }}**.

{{ ucfirst($label) }} is expected at about **{{ $expectedLocal }}** (your time). Keep an eye on
your device now — you've got until **{{ $failByLocal }}** to see it.

@component('mail::button', ['url' => $watchUrl])
Open the check
@endcomponent

When it arrives (or if it doesn't), come back and let us know.
@else
{{ ucfirst($label) }} for **{{ $scenarioTitle }}** was due by about **{{ $failByLocal }}** (your time).

**Did it show up on your device?**

@component('mail::button', ['url' => $watchUrl, 'color' => 'success'])
Yes — it worked
@endcomponent

@component('mail::button', ['url' => $missedUrl, 'color' => 'error'])
No — it never arrived
@endcomponent

If it never arrived, the second button opens the check with "Notification not received" already
noted — just confirm it with **Something was off**.
@endif

Thanks for helping test SmoothSeas,
The SmoothSeas team
@endcomponent
