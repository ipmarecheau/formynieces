<?php

namespace App\Filament\Resources\ChatConversations\Tables;

use App\Models\ChatConversation;
use Filament\Actions\Action;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ChatConversationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('visitor_name')
                    ->label('Parent')
                    ->searchable()
                    ->placeholder('Unnamed'),
                TextColumn::make('child_standard')
                    ->label('Standard')
                    ->placeholder('—'),
                TextColumn::make('worry')
                    ->label('Worry')
                    ->limit(40)
                    ->placeholder('—')
                    ->tooltip(fn (ChatConversation $record): ?string => $record->worry),
                TextColumn::make('contact')
                    ->label('Contact')
                    ->copyable()
                    ->placeholder('—'),
                TextColumn::make('messages_count')
                    ->counts('messages')
                    ->label('Msgs'),
                TextColumn::make('last_message_at')
                    ->label('Last activity')
                    ->since()
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => $state === 'open' ? 'warning' : 'success'),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options(array_combine(ChatConversation::STATUSES, ChatConversation::STATUSES)),
            ])
            ->actions([
                Action::make('view')
                    ->label('Transcript')
                    ->icon('heroicon-m-chat-bubble-left-right')
                    ->infolist([
                        TextEntry::make('visitor_name')->label('Parent'),
                        TextEntry::make('child_standard')->label('Standard')->placeholder('—'),
                        TextEntry::make('worry')->label('Worry')->placeholder('—'),
                        TextEntry::make('contact')->label('Contact')->placeholder('—')->copyable(),
                        RepeatableEntry::make('messages')
                            ->label('Transcript')
                            ->schema([
                                TextEntry::make('role')
                                    ->badge()
                                    ->formatStateUsing(fn (string $state): string => $state === 'bot' ? 'Smooth' : 'Parent')
                                    ->color(fn (string $state): string => $state === 'bot' ? 'info' : 'success'),
                                TextEntry::make('body')
                                    ->label('')
                                    ->columnSpanFull(),
                            ])
                            ->columns(1),
                    ])
                    ->modalHeading(fn (ChatConversation $record): string => 'Chat — '.($record->visitor_name ?: 'unnamed parent')),
                Action::make('close')
                    ->label('Mark closed')
                    ->icon('heroicon-m-check-circle')
                    ->color('success')
                    ->visible(fn (ChatConversation $record): bool => $record->status === 'open')
                    ->action(fn (ChatConversation $record) => $record->update(['status' => 'closed'])),
            ])
            ->defaultSort('last_message_at', 'desc');
    }
}
