<?php

namespace App\Filament\Resources\Lessons\Tables;

use App\Models\Lesson;
use App\Services\Lessons\LessonExporter;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LessonsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('module.topic')
                    ->label('Module')
                    ->searchable(),
                TextColumn::make('blocks')
                    ->label('Blocks')
                    ->state(fn ($record): int => is_array($record->blocks) ? count($record->blocks) : 0),
                IconColumn::make('is_published')
                    ->label('Published')
                    ->boolean(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
                Action::make('export')
                    ->icon(Heroicon::ArrowDownTray)
                    ->color('gray')
                    ->action(fn (Lesson $record): StreamedResponse => response()->streamDownload(
                        fn () => print (app(LessonExporter::class)->exportLesson($record)),
                        'lesson-'.($record->module?->code ?? $record->id).'.json',
                        ['Content-Type' => 'application/json'],
                    )),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
