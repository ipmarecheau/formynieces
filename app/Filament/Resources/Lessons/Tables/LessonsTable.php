<?php

namespace App\Filament\Resources\Lessons\Tables;

use App\Models\Lesson;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

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
                Action::make('preview')
                    ->label('Preview')
                    ->icon(Heroicon::Play)
                    ->color('primary')
                    ->url(fn (Lesson $record): string => route('admin.lessons.preview', $record->module_id))
                    ->openUrlInNewTab(),
                Action::make('previewReteach')
                    ->label('Re-teach')
                    ->icon(Heroicon::ArrowPath)
                    ->color('warning')
                    ->url(fn (Lesson $record): string => route('admin.lessons.preview-reteach', $record->module_id))
                    ->openUrlInNewTab(),
                EditAction::make(),
                Action::make('export')
                    ->label('Export')
                    ->icon(Heroicon::ArrowDownTray)
                    ->color('gray')
                    ->url(fn (Lesson $record): string => route('lessons.export', $record)),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
