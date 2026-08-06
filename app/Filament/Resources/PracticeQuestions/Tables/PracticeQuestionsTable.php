<?php

namespace App\Filament\Resources\PracticeQuestions\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class PracticeQuestionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('module.topic')
                    ->label('Module')
                    ->searchable()
                    ->wrap()
                    ->limit(40),
                TextColumn::make('prompt')
                    ->html()
                    ->limit(60)
                    ->searchable(),
                TextColumn::make('difficulty')
                    ->badge()
                    ->formatStateUsing(fn (int $state): string => [1 => 'Easy', 2 => 'Medium', 3 => 'Hard'][$state] ?? (string) $state)
                    ->color(fn (int $state): string => [1 => 'success', 2 => 'warning', 3 => 'danger'][$state] ?? 'gray')
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),
                TextColumn::make('source_ref')
                    ->label('Source')
                    ->placeholder('hand-authored')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),
            ])
            ->filters([
                SelectFilter::make('module_id')
                    ->label('Module')
                    ->relationship('module', 'topic')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('difficulty')
                    ->options([1 => 'Easy', 2 => 'Medium', 3 => 'Hard']),
                TernaryFilter::make('is_active')
                    ->label('Active'),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
