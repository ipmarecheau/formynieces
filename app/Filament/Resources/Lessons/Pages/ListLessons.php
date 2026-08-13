<?php

namespace App\Filament\Resources\Lessons\Pages;

use App\Filament\Resources\Lessons\LessonResource;
use App\Services\Lessons\LessonExporter;
use App\Services\Lessons\LessonImporter;
use App\Support\LessonBlockSchema;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ListLessons extends ListRecords
{
    protected static string $resource = LessonResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
            $this->importAction(),
            $this->exportAllAction(),
            $this->templateAction(),
            Action::make('guide')
                ->label('Import guide')
                ->icon(Heroicon::QuestionMarkCircle)
                ->color('gray')
                ->url(fn (): string => route('filament.admin.pages.lesson-import-guide')),
        ];
    }

    private function importAction(): Action
    {
        return Action::make('import')
            ->label('Import lessons')
            ->icon(Heroicon::ArrowUpTray)
            ->schema([
                FileUpload::make('file')
                    ->label('Lesson bundle (.json)')
                    ->acceptedFileTypes(['application/json', 'text/plain'])
                    ->storeFiles(false)
                    ->required(),
                Toggle::make('preview')
                    ->label('Preview only (validate, do not save)')
                    ->default(false),
            ])
            ->action(function (array $data): void {
                $json = (string) $data['file']->get();
                $preview = (bool) ($data['preview'] ?? false);

                $result = $preview
                    ? app(LessonImporter::class)->preview($json)
                    : app(LessonImporter::class)->import($json);

                if (! $result['ok']) {
                    Notification::make()->danger()->title('Import failed')->body($result['error'] ?? 'Invalid file.')->send();

                    return;
                }

                $errors = collect($result['lessons'])->flatMap(fn (array $l): array => $l['errors'])->take(8)->all();
                $verb = $preview ? 'Preview' : 'Imported';
                $body = "{$result['created']} new, {$result['updated']} updated, {$result['skipped']} skipped.";
                if ($errors !== []) {
                    $body .= "\n".implode("\n", $errors);
                }

                Notification::make()
                    ->status($result['skipped'] > 0 ? 'warning' : 'success')
                    ->title("{$verb} complete")
                    ->body($body)
                    ->persistent()
                    ->send();
            });
    }

    private function exportAllAction(): Action
    {
        return Action::make('exportAll')
            ->label('Export all')
            ->icon(Heroicon::ArrowDownTray)
            ->color('gray')
            ->action(fn (): StreamedResponse => response()->streamDownload(
                fn () => print (app(LessonExporter::class)->exportAll()),
                'lessons-'.now()->format('Y-m-d').'.json',
                ['Content-Type' => 'application/json'],
            ));
    }

    private function templateAction(): Action
    {
        return Action::make('template')
            ->label('Download template')
            ->icon(Heroicon::DocumentText)
            ->color('gray')
            ->action(fn (): StreamedResponse => response()->streamDownload(
                fn () => print ((string) json_encode([[
                    'module' => 'MATH-001',
                    'title' => 'Example lesson title',
                    'is_published' => true,
                    'blocks' => LessonBlockSchema::sampleBlocks(),
                ]], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)),
                'lesson-import-template.json',
                ['Content-Type' => 'application/json'],
            ));
    }
}
