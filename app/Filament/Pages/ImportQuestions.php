<?php

namespace App\Filament\Pages;

use App\Services\QuestionBank\QuestionImportCoordinator;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Storage;

/**
 * Admin tool to grow the practice question bank from a Moodle XML export. Upload
 * a file, preview it as a dry run, then import for real — safely and repeatedly.
 */
class ImportQuestions extends Page
{
    protected string $view = 'filament.pages.import-questions';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::ArrowUpTray;

    protected static ?string $navigationLabel = 'Import Questions';

    protected static ?string $title = 'Import Questions';

    /**
     * The last run's outcome, kept for the page to render.
     *
     * @var array<string, mixed>|null
     */
    public ?array $report = null;

    /**
     * @return array<int, Action>
     */
    protected function getHeaderActions(): array
    {
        return [
            Action::make('import')
                ->label('Upload Moodle XML')
                ->icon(Heroicon::ArrowUpTray)
                ->modalHeading('Import questions from Moodle XML')
                ->modalSubmitActionLabel('Run')
                ->schema([
                    FileUpload::make('xml')
                        ->label('Moodle XML file')
                        ->helperText('Multiple-choice questions load into the practice bank; essay questions load into the writing-prompt bank. A file may contain both.')
                        ->disk('local')
                        ->directory('question-import-tmp')
                        ->acceptedFileTypes(['application/xml', 'text/xml', 'text/plain'])
                        ->maxSize(25600) // 25 MB — banks with embedded figures are large
                        ->required(),
                    Toggle::make('dry_run')
                        ->label('Preview only (dry run) — nothing is saved')
                        ->default(true),
                ])
                ->action(function (array $data): void {
                    $path = $data['xml'];
                    $xml = Storage::disk('local')->get($path);
                    $dryRun = (bool) ($data['dry_run'] ?? true);

                    $result = app(QuestionImportCoordinator::class)->import((string) $xml, $dryRun);

                    Storage::disk('local')->delete($path);

                    $this->report = [
                        'dryRun' => $result->dryRun,
                        'summary' => $result->summary(),
                        'parsed' => $result->parsed,
                        'created' => $result->created,
                        'updated' => $result->updated,
                        'imagesStored' => $result->imagesStored,
                        'skipped' => $result->skipped,
                        'byModule' => $result->byModule,
                    ];

                    Notification::make()
                        ->title($result->dryRun ? 'Preview complete — nothing saved' : 'Import complete')
                        ->body($result->summary())
                        ->success()
                        ->persistent()
                        ->send();
                }),
        ];
    }
}
