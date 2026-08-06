<x-filament::page>
    <div class="fi-section-content-ctn">
        <x-filament::section>
            <x-slot name="heading">How it works</x-slot>

            <div class="prose dark:prose-invert max-w-none text-sm">
                <p>Upload a <strong>Moodle XML</strong> export to add SEA practice questions to the bank.
                   Use <em>Preview (dry run)</em> first to see exactly what will happen, then run it for real.</p>
                <ul>
                    <li>Questions are grouped under Moodle categories named <code>Qnn &lt;label&gt;</code>
                        (e.g. <code>Q04 Fraction shaded</code>); the <code>Qnn</code> code selects the syllabus module.</li>
                    <li>Difficulty is read from the question name (<code>… · D1 · …</code> to <code>D5</code>)
                        and mapped to the three practice rungs.</li>
                    <li>Only single-answer, 4-option <code>multichoice</code> questions import; anything else is
                        skipped and listed below.</li>
                    <li>Embedded figures are extracted automatically. Re-uploading a file updates existing
                        questions instead of duplicating them.</li>
                </ul>
            </div>
        </x-filament::section>

        @if ($report)
            <x-filament::section class="mt-4">
                <x-slot name="heading">
                    {{ $report['dryRun'] ? 'Preview result (nothing saved)' : 'Import result' }}
                </x-slot>

                <p class="text-sm font-semibold mb-3">{{ $report['summary'] }}</p>

                <div class="grid grid-cols-2 gap-3 sm:grid-cols-5 text-center">
                    @foreach ([
                        'Parsed' => $report['parsed'],
                        ($report['dryRun'] ? 'Would create' : 'Created') => $report['created'],
                        ($report['dryRun'] ? 'Would update' : 'Updated') => $report['updated'],
                        'Skipped' => count($report['skipped']),
                        'Images' => $report['imagesStored'],
                    ] as $label => $value)
                        <div class="rounded-lg border border-gray-200 dark:border-white/10 p-3">
                            <div class="text-2xl font-bold">{{ $value }}</div>
                            <div class="text-xs text-gray-500">{{ $label }}</div>
                        </div>
                    @endforeach
                </div>

                @if (count($report['skipped']) > 0)
                    <div class="mt-5">
                        <h3 class="text-sm font-semibold mb-2">Skipped questions</h3>
                        <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-white/10">
                            <table class="w-full text-sm">
                                <thead class="bg-gray-50 dark:bg-white/5 text-left">
                                    <tr>
                                        <th class="px-3 py-2 font-medium">Question</th>
                                        <th class="px-3 py-2 font-medium">Reason</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($report['skipped'] as $row)
                                        <tr class="border-t border-gray-100 dark:border-white/5">
                                            <td class="px-3 py-2 font-mono text-xs">{{ $row['ref'] }}</td>
                                            <td class="px-3 py-2">{{ $row['reason'] }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif
            </x-filament::section>
        @endif
    </div>
</x-filament::page>
