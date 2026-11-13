<?php

namespace App\Filament\Resources\PersonaRuns;

use App\Filament\Resources\PersonaRuns\Pages\ListPersonaRuns;
use App\Filament\Resources\PersonaRuns\Tables\PersonaRunsTable;
use App\Models\PersonaRun;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

/**
 * PA-01..03 — the run log of the nightly persona simulations: persona, status, findings and
 * cost against the monthly budget. Read-only; findings are reviewed in Persona findings.
 */
class PersonaRunsResource extends Resource
{
    protected static ?string $model = PersonaRun::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBeaker;

    protected static string|UnitEnum|null $navigationGroup = 'Quality';

    protected static ?string $navigationLabel = 'Persona runs';

    protected static ?string $modelLabel = 'persona run';

    public static function table(Table $table): Table
    {
        return PersonaRunsTable::configure($table);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPersonaRuns::route('/'),
        ];
    }
}
