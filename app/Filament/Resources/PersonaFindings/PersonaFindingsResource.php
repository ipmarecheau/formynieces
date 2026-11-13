<?php

namespace App\Filament\Resources\PersonaFindings;

use App\Filament\Resources\PersonaFindings\Pages\ListPersonaFindings;
use App\Filament\Resources\PersonaFindings\Tables\PersonaFindingsTable;
use App\Models\PersonaFinding;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

/**
 * PA-04/05 — the team reviews and critiques what the nightly personas found: severity, area,
 * observation and suggestion, with a verdict (valid / invalid / won't fix / fixed) + notes.
 */
class PersonaFindingsResource extends Resource
{
    protected static ?string $model = PersonaFinding::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSparkles;

    protected static string|UnitEnum|null $navigationGroup = 'Quality';

    protected static ?string $navigationLabel = 'Persona findings';

    protected static ?string $modelLabel = 'persona finding';

    public static function getNavigationBadge(): ?string
    {
        $unreviewed = static::getModel()::whereNull('admin_verdict')->count();

        return $unreviewed > 0 ? (string) $unreviewed : null;
    }

    public static function table(Table $table): Table
    {
        return PersonaFindingsTable::configure($table);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPersonaFindings::route('/'),
        ];
    }
}
