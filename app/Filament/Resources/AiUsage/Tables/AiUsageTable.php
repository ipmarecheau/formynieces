<?php

namespace App\Filament\Resources\AiUsage\Tables;

use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AiUsageTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('student.name')
                    ->label('Student')
                    ->searchable(),
                TextColumn::make('input_tokens')
                    ->label('Input tokens')
                    ->numeric()
                    ->sortable()
                    ->summarize(Sum::make()->label('Total')),
                TextColumn::make('output_tokens')
                    ->label('Output tokens')
                    ->numeric()
                    ->sortable()
                    ->summarize(Sum::make()->label('Total')),
                TextColumn::make('cost_usd')
                    ->label('Spend (MTD)')
                    ->money('USD')
                    ->badge()
                    ->color(fn ($record): string => match (true) {
                        (float) $record->cost_usd >= 1.50 => 'danger',   // at the hard ceiling
                        (float) $record->cost_usd >= 1.00 => 'warning',  // past the soft cap
                        default => 'success',
                    })
                    ->sortable()
                    ->summarize(Sum::make()->label('Total spend')->money('USD')),
            ])
            ->defaultSort('cost_usd', 'desc');
    }
}
