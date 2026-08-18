<?php

namespace App\Filament\Resources\ReadingPassages\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class ReadingPassagesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('reading_level')
                    ->label('Level')
                    ->badge()
                    ->sortable(),
                TextColumn::make('word_count')
                    ->label('Words')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('questions')
                    ->label('Questions')
                    ->state(fn ($record): int => is_array($record->questions) ? count($record->questions) : 0),
                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),
            ])
            ->filters([
                SelectFilter::make('reading_level')
                    ->label('Reading level')
                    ->options(array_combine(range(1, 6), range(1, 6))),
                TernaryFilter::make('is_active')
                    ->label('Available to serve'),
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
