<?php

namespace App\Filament\Resources\OnboardingCalls\Tables;

use App\Models\OnboardingCall;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class OnboardingCallsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('call_date')
                    ->label('Day')
                    ->date('D j M Y')
                    ->sortable(),
                TextColumn::make('call_time')
                    ->label('Time (TT)')
                    ->state(fn (OnboardingCall $record): string => $record->call_time->format('g:ia')),
                TextColumn::make('parent_name')
                    ->label('Parent')
                    ->searchable(),
                TextColumn::make('email')
                    ->searchable()
                    ->copyable(),
                TextColumn::make('phone')
                    ->copyable(),
                TextColumn::make('child_standard')
                    ->label('Standard')
                    ->placeholder('—'),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'confirmed' => 'info',
                        'completed' => 'success',
                        'cancelled' => 'danger',
                        default => 'warning',
                    }),
                TextColumn::make('notes')
                    ->limit(50)
                    ->placeholder('—')
                    ->tooltip(fn (OnboardingCall $record): ?string => $record->notes),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options(array_combine(OnboardingCall::STATUSES, OnboardingCall::STATUSES)),
            ])
            ->actions([
                EditAction::make(),
            ])
            ->defaultSort('call_date', 'asc')
            ->modifyQueryUsing(fn ($query) => $query->orderBy('call_time'));
    }
}
