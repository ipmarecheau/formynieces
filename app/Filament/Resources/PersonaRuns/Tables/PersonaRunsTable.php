<?php

namespace App\Filament\Resources\PersonaRuns\Tables;

use App\Models\PersonaRun;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class PersonaRunsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('persona')
                    ->badge()
                    ->color('gray'),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        PersonaRun::STATUS_COMPLETED => 'success',
                        PersonaRun::STATUS_RUNNING => 'info',
                        PersonaRun::STATUS_SKIPPED_BUDGET => 'warning',
                        default => 'danger',
                    }),
                TextColumn::make('findings_count')
                    ->label('Findings')
                    ->counts('findings'),
                TextColumn::make('cost_usd')
                    ->label('Cost')
                    ->money('USD'),
                TextColumn::make('summary')
                    ->limit(70)
                    ->tooltip(fn (PersonaRun $r): ?string => $r->summary)
                    ->toggleable(),
                TextColumn::make('started_at')
                    ->label('Started')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('persona')
                    ->options([
                        PersonaRun::PERSONA_ANXIOUS_PARENT => 'Anxious parent',
                        PersonaRun::PERSONA_CHILD_FULL_DAY => "Child's full day",
                    ]),
                SelectFilter::make('status')
                    ->options([
                        PersonaRun::STATUS_COMPLETED => 'Completed',
                        PersonaRun::STATUS_FAILED => 'Failed',
                        PersonaRun::STATUS_SKIPPED_BUDGET => 'Skipped (budget)',
                    ]),
            ])
            ->defaultSort('started_at', 'desc');
    }
}
