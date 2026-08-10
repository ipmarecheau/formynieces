<?php

namespace App\Filament\Resources\PracticeQuestions\Pages;

use App\Filament\Resources\PracticeQuestions\PracticeQuestionResource;
use App\Models\PracticeQuestion;
use App\Services\QuestionBank\MoodleQuestionExporter;
use App\Services\QuestionBank\QuestionBankBackupService;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ListPracticeQuestions extends ListRecords
{
    protected static string $resource = PracticeQuestionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
            Action::make('export')
                ->label('Export to Moodle XML')
                ->icon(Heroicon::ArrowDownTray)
                ->color('gray')
                ->action(fn (): StreamedResponse => response()->streamDownload(
                    fn () => print (app(MoodleQuestionExporter::class)->export()),
                    'sea_question_bank_'.now()->format('Y-m-d').'.xml',
                    ['Content-Type' => 'application/xml'],
                )),
            Action::make('backupNow')
                ->label('Back up now')
                ->icon(Heroicon::ArchiveBox)
                ->color('gray')
                ->action(function (): void {
                    $backup = app(QuestionBankBackupService::class)->backup('manual');
                    Notification::make()
                        ->success()
                        ->title('Backup taken')
                        ->body("{$backup->question_count} questions snapshotted. Restore any time from Question Bank Backups.")
                        ->send();
                }),
            Action::make('deleteAll')
                ->label('Delete all questions')
                ->icon(Heroicon::Trash)
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('Delete every question in the bank?')
                ->modalDescription('A safety backup is taken first, so you can restore this exact state from Question Bank Backups. This cannot be undone otherwise.')
                ->modalSubmitActionLabel('Back up and delete all')
                ->action(function (): void {
                    $backup = app(QuestionBankBackupService::class)->deleteAll();
                    Notification::make()
                        ->success()
                        ->title('Bank emptied')
                        ->body("Backed up {$backup->question_count} questions first — restorable from Question Bank Backups.")
                        ->send();
                })
                ->visible(fn (): bool => PracticeQuestion::exists()),
        ];
    }
}
