<?php

namespace App\Filament\Resources\PushDeliveries\Tables;

use App\Models\PushDelivery;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class PushDeliveriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')
                    ->label('When')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('user.name')
                    ->label('To')
                    ->searchable(),
                TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->searchable(),
                TextColumn::make('criticality')
                    ->badge()
                    ->color(fn (PushDelivery $record): string => $record->criticality === 'critical' ? 'warning' : 'gray'),
                TextColumn::make('platform')
                    ->badge(),
                TextColumn::make('outcome')
                    ->badge()
                    ->color(fn (PushDelivery $record): string => match ($record->outcome) {
                        PushDelivery::OUTCOME_DELIVERED => 'success',
                        PushDelivery::OUTCOME_SUPPRESSED => 'gray',
                        PushDelivery::OUTCOME_RETRYABLE => 'warning',
                        default => 'danger',
                    }),
                TextColumn::make('reason')
                    ->label('Reason')
                    ->toggleable()
                    ->placeholder('—'),
            ])
            ->filters([
                SelectFilter::make('outcome')
                    ->options([
                        PushDelivery::OUTCOME_DELIVERED => 'Delivered',
                        PushDelivery::OUTCOME_SUPPRESSED => 'Suppressed',
                        PushDelivery::OUTCOME_FAILED => 'Failed',
                        PushDelivery::OUTCOME_RETRYABLE => 'Retryable',
                    ]),
                SelectFilter::make('criticality')
                    ->options(['standard' => 'Standard', 'critical' => 'Critical']),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
