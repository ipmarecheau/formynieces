<?php

namespace App\Filament\Resources\PersonaFindings\Tables;

use App\Models\PersonaFinding;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class PersonaFindingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('run.persona')
                    ->label('Persona')
                    ->badge()
                    ->color('gray'),
                TextColumn::make('severity')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'high' => 'danger',
                        'medium' => 'warning',
                        default => 'gray',
                    }),
                TextColumn::make('area')
                    ->badge()
                    ->color('info'),
                TextColumn::make('observation')
                    ->limit(80)
                    ->tooltip(fn (PersonaFinding $r): ?string => $r->observation)
                    ->searchable(),
                TextColumn::make('suggestion')
                    ->limit(60)
                    ->placeholder('—')
                    ->toggleable(),
                TextColumn::make('admin_verdict')
                    ->label('Verdict')
                    ->badge()
                    ->placeholder('unreviewed')
                    ->color(fn (?string $state): string => match ($state) {
                        PersonaFinding::VERDICT_VALID => 'warning',
                        PersonaFinding::VERDICT_FIXED => 'success',
                        PersonaFinding::VERDICT_INVALID, PersonaFinding::VERDICT_WONT_FIX => 'gray',
                        default => 'danger',
                    }),
                TextColumn::make('created_at')
                    ->label('Found')
                    ->since()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('severity')
                    ->options(['high' => 'High', 'medium' => 'Medium', 'low' => 'Low']),
                SelectFilter::make('admin_verdict')
                    ->label('Verdict')
                    ->options([
                        PersonaFinding::VERDICT_VALID => 'Valid',
                        PersonaFinding::VERDICT_INVALID => 'Invalid',
                        PersonaFinding::VERDICT_WONT_FIX => "Won't fix",
                        PersonaFinding::VERDICT_FIXED => 'Fixed',
                    ]),
            ])
            ->recordActions([
                Action::make('critique')
                    ->label('Critique')
                    ->icon('heroicon-m-chat-bubble-left-right')
                    ->color('primary')
                    ->schema([
                        Select::make('admin_verdict')
                            ->label('Verdict')
                            ->options([
                                PersonaFinding::VERDICT_VALID => 'Valid — worth fixing',
                                PersonaFinding::VERDICT_INVALID => 'Invalid — not a real issue',
                                PersonaFinding::VERDICT_WONT_FIX => "Won't fix",
                                PersonaFinding::VERDICT_FIXED => 'Fixed',
                            ])
                            ->required(),
                        Textarea::make('admin_notes')
                            ->label('Notes')
                            ->rows(3),
                    ])
                    ->fillForm(fn (PersonaFinding $r): array => [
                        'admin_verdict' => $r->admin_verdict,
                        'admin_notes' => $r->admin_notes,
                    ])
                    ->action(fn (array $data, PersonaFinding $r) => $r->update([
                        'admin_verdict' => $data['admin_verdict'],
                        'admin_notes' => $data['admin_notes'] ?? null,
                    ])),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
