<x-filament-panels::page>
    <div class="max-w-md">
        <label class="fi-fo-field-wrp-label text-sm font-medium text-gray-950 dark:text-white mb-1 block">
            Student
        </label>
        <select
            wire:model.live="studentId"
            class="fi-input block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-base text-gray-950 dark:border-white/10 dark:bg-white/5 dark:text-white"
        >
            <option value="">Choose a student…</option>
            @foreach ($this->students() as $id => $name)
                <option value="{{ $id }}">{{ $name }}</option>
            @endforeach
        </select>
    </div>

    @php($events = $this->events())

    @if ($studentId && empty($events))
        <p class="text-sm text-gray-500 dark:text-gray-400">No recent activity for this student yet.</p>
    @elseif ($studentId)
        <ol class="relative space-y-3 border-s border-gray-200 ps-5 dark:border-white/10">
            @foreach ($events as $event)
                <li class="relative">
                    <span @class([
                        'absolute -start-[1.4rem] mt-1.5 h-2.5 w-2.5 rounded-full ring-2 ring-white dark:ring-gray-900',
                        'bg-success-500' => $event->tone === 'positive',
                        'bg-danger-500' => $event->tone === 'negative',
                        'bg-gray-400' => $event->tone === 'neutral',
                    ])></span>
                    <div class="flex flex-col gap-0.5">
                        <span class="text-sm text-gray-950 dark:text-white">{{ $event->summary }}</span>
                        <span class="text-xs text-gray-500 dark:text-gray-400" title="{{ $event->at->toDayDateTimeString() }}">
                            {{ $event->at->diffForHumans() }}
                        </span>
                    </div>
                </li>
            @endforeach
        </ol>
    @else
        <p class="text-sm text-gray-500 dark:text-gray-400">Pick a student to see their recent activity.</p>
    @endif
</x-filament-panels::page>
