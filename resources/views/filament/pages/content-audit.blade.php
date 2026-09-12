<x-filament-panels::page>
    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
        @foreach(['lessons' => 'Lessons', 'practice' => 'Practice', 'reading' => 'Reading', 'writing' => 'Writing'] as $key => $label)
            @php($item = $report[$key])
            <x-filament::section>
                <x-slot name="heading">{{ $label }}</x-slot>
                <div class="text-2xl font-bold">{{ $item['pct'] ?? 0 }}%</div>
                <p class="text-sm text-gray-500">{{ $item['have'] ?? ($item['masterable'] ?? 0) }} of {{ $item['need'] ?? 0 }} target</p>
            </x-filament::section>
        @endforeach
        @php($papers = $report['past_papers'])
        <x-filament::section>
            <x-slot name="heading">Past paper bank</x-slot>
            <div class="grid grid-cols-2 gap-3 text-sm">
                <div><strong>{{ $papers['total'] }}</strong><br><span class="text-gray-500">total questions</span></div>
                <div><strong>{{ $papers['approved'] }}</strong><br><span class="text-gray-500">approved</span></div>
                <div><strong>{{ $papers['pending'] }}</strong><br><span class="text-gray-500">awaiting QC</span></div>
                <div><strong>{{ $papers['unmapped'] }}</strong><br><span class="text-gray-500">need topic mapping</span></div>
            </div>
        </x-filament::section>
        @php($sources = $report['past_paper_sources'])
        <x-filament::section>
            <x-slot name="heading">Source intake</x-slot>
            <p class="text-2xl font-bold">{{ $sources['files'] }} PDFs</p>
            <p class="text-sm text-gray-500">{{ $sources['text_extracted'] }} with text · {{ $sources['needs_ocr'] }} needing OCR</p>
        </x-filament::section>
    </div>
    <x-filament::section class="mt-4">
        <x-slot name="heading">Past paper intake by subject</x-slot>
        <div class="overflow-x-auto"><table class="w-full text-sm"><thead><tr class="text-left"><th class="p-2">Subject</th><th class="p-2">Papers</th><th class="p-2">Questions</th></tr></thead><tbody>@forelse($papers['bySubject'] as $subject => $row)<tr class="border-t"><td class="p-2 font-medium">{{ $subject }}</td><td class="p-2">{{ $row['papers'] }}</td><td class="p-2">{{ $row['questions'] }}</td></tr>@empty<tr><td class="p-2 text-gray-500" colspan="3">No past-paper questions imported yet.</td></tr>@endforelse</tbody></table></div>
    </x-filament::section>
    <x-filament::section class="mt-4">
        <x-slot name="heading">How this audit supports SEA preparation</x-slot>
        <p class="text-sm text-gray-600 dark:text-gray-300">Questions move from source intake to topic mapping, controlled variants, admin QC, and publication. Once a student has covered a topic cluster, the practice engine can use approved variants for maintenance. Guardians can then issue weekly printable papers, upload solutions, and review the resulting topic signals.</p>
    </x-filament::section>
</x-filament-panels::page>
