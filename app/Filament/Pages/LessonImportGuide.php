<?php

namespace App\Filament\Pages;

use App\Support\LessonBlockSchema;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * A self-service reference for bulk lesson import (LB-04): what the JSON looks like, every block
 * type and its fields, how module codes work, and a downloadable template. Generated from
 * LessonBlockSchema so it can never drift from what the importer actually accepts.
 */
class LessonImportGuide extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedQuestionMarkCircle;

    protected static ?string $navigationLabel = 'Lesson import guide';

    protected static ?string $title = 'Lesson import guide';

    protected string $view = 'filament.pages.lesson-import-guide';

    /**
     * type => required fields, for the reference table.
     *
     * @return array<string, array<int, string>>
     */
    public function blockTypes(): array
    {
        return LessonBlockSchema::REQUIRED;
    }

    /** Pretty-printed sample bundle shown inline and offered as the template download. */
    public function sampleJson(): string
    {
        return (string) json_encode([[
            'module' => 'MATH-001',
            'title' => 'Example lesson title',
            'is_published' => true,
            'blocks' => LessonBlockSchema::sampleBlocks(),
        ]], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('template')
                ->label('Download template')
                ->icon(Heroicon::DocumentText)
                ->action(fn (): StreamedResponse => response()->streamDownload(
                    fn () => print ($this->sampleJson()),
                    'lesson-import-template.json',
                    ['Content-Type' => 'application/json'],
                )),
        ];
    }
}
