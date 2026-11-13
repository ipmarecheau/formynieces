<?php

namespace App\Filament\Resources\SupportTickets\Tables;

use App\Models\SupportTicket;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class SupportTicketsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        SupportTicket::STATUS_OPEN => 'danger',
                        SupportTicket::STATUS_TRIAGED => 'warning',
                        SupportTicket::STATUS_RESOLVED => 'success',
                        default => 'gray',
                    }),
                TextColumn::make('kind')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'issue' => 'danger',
                        'question' => 'info',
                        'idea' => 'success',
                        default => 'gray',
                    }),
                TextColumn::make('role')
                    ->label('From')
                    ->badge()
                    ->color('gray'),
                TextColumn::make('user.name')
                    ->label('User')
                    ->placeholder('Guest')
                    ->searchable(),
                IconColumn::make('audio_path')
                    ->label('Voice')
                    ->boolean()
                    ->trueIcon('heroicon-m-microphone')
                    ->falseIcon('heroicon-m-minus')
                    ->getStateUsing(fn (SupportTicket $r): bool => filled($r->audio_path)),
                TextColumn::make('message')
                    ->limit(70)
                    ->placeholder('— voice note —')
                    ->tooltip(fn (SupportTicket $r): ?string => $r->message)
                    ->searchable(),
                TextColumn::make('url')
                    ->label('Where')
                    ->limit(28)
                    ->tooltip(fn (SupportTicket $r): ?string => $r->url)
                    ->toggleable(),
                TextColumn::make('student.name')
                    ->label('Child')
                    ->placeholder('—')
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->label('When')
                    ->since()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        SupportTicket::STATUS_OPEN => 'Open',
                        SupportTicket::STATUS_TRIAGED => 'Triaged',
                        SupportTicket::STATUS_RESOLVED => 'Resolved',
                    ]),
                SelectFilter::make('kind')
                    ->options(['issue' => 'Something\'s wrong', 'question' => 'Question', 'idea' => 'Idea']),
            ])
            ->recordActions([
                Action::make('playAudio')
                    ->label('Play')
                    ->icon('heroicon-m-play')
                    ->color('info')
                    ->url(fn (SupportTicket $r): string => route('support.audio', $r))
                    ->openUrlInNewTab()
                    ->visible(fn (SupportTicket $r): bool => filled($r->audio_path)),
                Action::make('triage')
                    ->label('Triage')
                    ->icon('heroicon-m-eye')
                    ->color('warning')
                    ->visible(fn (SupportTicket $r): bool => $r->status === SupportTicket::STATUS_OPEN)
                    ->action(fn (SupportTicket $r) => $r->update(['status' => SupportTicket::STATUS_TRIAGED])),
                Action::make('resolve')
                    ->label('Resolve')
                    ->icon('heroicon-m-check-circle')
                    ->color('success')
                    ->visible(fn (SupportTicket $r): bool => $r->status !== SupportTicket::STATUS_RESOLVED)
                    ->schema([
                        Textarea::make('admin_notes')
                            ->label('Resolution notes (optional)')
                            ->rows(3),
                    ])
                    ->fillForm(fn (SupportTicket $r): array => ['admin_notes' => $r->admin_notes])
                    ->action(fn (array $data, SupportTicket $r) => $r->update([
                        'status' => SupportTicket::STATUS_RESOLVED,
                        'admin_notes' => $data['admin_notes'] ?? $r->admin_notes,
                    ])),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
