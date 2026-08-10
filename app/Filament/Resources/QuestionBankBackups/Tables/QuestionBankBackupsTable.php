<?php

namespace App\Filament\Resources\QuestionBankBackups\Tables;

use App\Models\QuestionBankBackup;
use App\Services\QuestionBank\QuestionBankBackupService;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class QuestionBankBackupsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('created_at')
                    ->label('Taken')
                    ->dateTime('D j M Y, g:i a')
                    ->sortable(),
                TextColumn::make('reason')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'daily' => 'success',
                        'before-delete-all', 'before-restore' => 'warning',
                        default => 'gray',
                    }),
                TextColumn::make('question_count')
                    ->label('Questions')
                    ->numeric()
                    ->sortable(),
            ])
            ->recordActions([
                Action::make('restore')
                    ->icon(Heroicon::ArrowUturnLeft)
                    ->requiresConfirmation()
                    ->modalHeading('Restore the bank to this backup?')
                    ->modalDescription('This replaces every current question with the ones captured in this backup. A safety backup of the current bank is taken first.')
                    ->modalSubmitActionLabel('Restore')
                    ->action(function (QuestionBankBackup $record): void {
                        app(QuestionBankBackupService::class)->restore($record);
                        Notification::make()
                            ->success()
                            ->title('Bank restored')
                            ->body("Restored {$record->question_count} questions from ".$record->created_at->format('j M Y, g:i a').'.')
                            ->send();
                    }),
                Action::make('download')
                    ->icon(Heroicon::ArrowDownTray)
                    ->color('gray')
                    ->action(fn (QuestionBankBackup $record): StreamedResponse => response()->streamDownload(
                        fn () => print (Storage::disk('local')->get($record->path)),
                        'question-bank-backup-'.$record->created_at->format('Y-m-d_His').'.json',
                        ['Content-Type' => 'application/json'],
                    )),
                DeleteAction::make()
                    ->before(fn (QuestionBankBackup $record) => Storage::disk('local')->delete($record->path)),
            ]);
    }
}
