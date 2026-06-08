<?php

namespace App\Filament\Resources\WeeklyTargets;

use App\Filament\Resources\WeeklyTargets\Pages\CreateWeeklyTarget;
use App\Filament\Resources\WeeklyTargets\Pages\EditWeeklyTarget;
use App\Filament\Resources\WeeklyTargets\Pages\ListWeeklyTargets;
use App\Filament\Resources\WeeklyTargets\Schemas\WeeklyTargetForm;
use App\Filament\Resources\WeeklyTargets\Tables\WeeklyTargetsTable;
use App\Models\WeeklyTarget;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class WeeklyTargetResource extends Resource
{
    protected static ?string $model = WeeklyTarget::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'week_start_date';

    public static function form(Schema $schema): Schema
    {
        return WeeklyTargetForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return WeeklyTargetsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListWeeklyTargets::route('/'),
            'create' => CreateWeeklyTarget::route('/create'),
            'edit' => EditWeeklyTarget::route('/{record}/edit'),
        ];
    }
}
