<?php

namespace App\Filament\Resources\ContactMessages\Tables;

use App\Models\ContactMessage;
use Filament\Actions\Action;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ContactMessagesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                IconColumn::make('handled')
                    ->label('Handled')
                    ->boolean(),
                TextColumn::make('name')
                    ->label('From')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->searchable()
                    ->copyable(),
                TextColumn::make('topic')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'onboarding' => 'info',
                        'billing' => 'warning',
                        'technical' => 'danger',
                        'feedback' => 'success',
                        default => 'gray',
                    }),
                TextColumn::make('message')
                    ->limit(80)
                    ->tooltip(fn (ContactMessage $record): string => $record->message),
                TextColumn::make('created_at')
                    ->label('Received')
                    ->since()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Action::make('markHandled')
                    ->label('Mark handled')
                    ->icon('heroicon-m-check-circle')
                    ->color('success')
                    ->visible(fn (ContactMessage $record): bool => ! $record->handled)
                    ->action(fn (ContactMessage $record) => $record->update(['handled' => true])),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
