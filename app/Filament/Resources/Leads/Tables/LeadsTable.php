<?php

namespace App\Filament\Resources\Leads\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

/**
 * The admin leads panel (lead_capture.feature LG-14/LG-11): every captured lead with its
 * placement snapshot, segmentable by weakest strand, and whether it converted to a trial.
 */
class LeadsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('email')->searchable()->copyable(),
                TextColumn::make('whatsapp')->label('WhatsApp')->toggleable()->placeholder('—'),
                TextColumn::make('child_level')->label('Class')->badge()->toggleable(),
                TextColumn::make('mock_score')->label('Score')->suffix('%')->sortable()->placeholder('—'),
                TextColumn::make('placement_band')->label('Placement')->wrap()->placeholder('mock not taken'),
                TextColumn::make('weakest_strands')
                    ->label('Weakest strands')
                    ->badge()
                    ->separator(',')
                    ->placeholder('—'),
                IconColumn::make('weekly_opt_in')->label('Weekly')->boolean()->toggleable(),
                IconColumn::make('converted_at')->label('Trial')->boolean()->state(fn ($record) => $record->converted_at !== null),
                TextColumn::make('created_at')->label('Captured')->since()->sortable(),
            ])
            ->filters([
                TernaryFilter::make('converted')
                    ->label('Converted to trial')
                    ->queries(
                        true: fn (Builder $q) => $q->whereNotNull('converted_at'),
                        false: fn (Builder $q) => $q->whereNull('converted_at'),
                    ),
                TernaryFilter::make('weekly_opt_in')->label('Weekly nurture'),
                Filter::make('has_report')
                    ->label('Completed the mock')
                    ->query(fn (Builder $q) => $q->whereNotNull('placement_band')),
            ])
            ->recordActions([])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
