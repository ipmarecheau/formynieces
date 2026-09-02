<x-mail::message>
# Your child's SEA placement report

Thanks for taking the free SEA mock. Here's where **{{ $lead->child_level ?? 'your child' }}** stands right now.

<x-mail::panel>
**{{ $lead->placement_band }}** — projected first-choice readiness{{ $lead->mock_score !== null ? ' ('.$lead->mock_score.'%)' : '' }}.
</x-mail::panel>

@if (! empty($lead->weakest_strands))
**The three strands to fix first:**
@foreach ($lead->weakest_strands as $strand)
- {{ $strand }}
@endforeach
@endif

**Your one next step:** {{ $lead->next_step }}

<x-mail::button :url="route('placement-report')">
Start your free month
</x-mail::button>

A full month free — no card to begin — plus your AI practice pack. Built for the T&T SEA syllabus.

Smooth 🐢<br>
{{ config('app.name') }}
</x-mail::message>
