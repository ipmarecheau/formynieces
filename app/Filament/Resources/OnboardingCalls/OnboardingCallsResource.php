<?php

namespace App\Filament\Resources\OnboardingCalls;

use App\Filament\Resources\OnboardingCalls\Pages\ListOnboardingCalls;
use App\Filament\Resources\OnboardingCalls\Tables\OnboardingCallsTable;
use App\Models\OnboardingCall;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

/**
 * OC-05 — the founder's call calendar: every booked onboarding call with day,
 * time, parent and child's standard, and a status that moves requested →
 * confirmed → completed (or cancelled).
 */
class OnboardingCallsResource extends Resource
{
    protected static ?string $model = OnboardingCall::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPhone;

    protected static ?string $navigationLabel = 'Onboarding Calls';

    protected static string|UnitEnum|null $navigationGroup = 'Website';

    protected static ?string $modelLabel = 'onboarding call';

    public static function table(Table $table): Table
    {
        return OnboardingCallsTable::configure($table);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListOnboardingCalls::route('/'),
        ];
    }
}
