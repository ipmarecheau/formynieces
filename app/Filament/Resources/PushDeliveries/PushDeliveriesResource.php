<?php

namespace App\Filament\Resources\PushDeliveries;

use App\Filament\Resources\PushDeliveries\Pages\ListPushDeliveries;
use App\Filament\Resources\PushDeliveries\Tables\PushDeliveriesTable;
use App\Models\PushDelivery;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

/**
 * PN-08 — read-only admin view of every push attempt (delivered / suppressed / failed /
 * retryable), so the team can see, critique and debug what the self-hosted sender sent.
 */
class PushDeliveriesResource extends Resource
{
    protected static ?string $model = PushDelivery::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBellAlert;

    protected static string|UnitEnum|null $navigationGroup = 'Website';

    protected static ?string $navigationLabel = 'Push log';

    protected static ?string $modelLabel = 'push delivery';

    protected static ?string $pluralModelLabel = 'push deliveries';

    public static function table(Table $table): Table
    {
        return PushDeliveriesTable::configure($table);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPushDeliveries::route('/'),
        ];
    }
}
