<?php

namespace App\Filament\Resources\AiUsage;

use App\Filament\Resources\AiUsage\Pages\ListAiUsage;
use App\Filament\Resources\AiUsage\Tables\AiUsageTable;
use App\Models\StudentLlmUsage;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

/**
 * AG-09 — read-only admin panel of per-student LLM usage this month: tokens and spend
 * against the USD 1.00 (discretionary) and 1.50 (hard) caps, with a roll-up total.
 */
class AiUsageResource extends Resource
{
    protected static ?string $model = StudentLlmUsage::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCpuChip;

    protected static ?string $navigationLabel = 'AI Usage';

    protected static ?string $modelLabel = 'AI usage';

    protected static ?string $pluralModelLabel = 'AI usage';

    public static function table(Table $table): Table
    {
        return AiUsageTable::configure($table);
    }

    /** Only this month's ledger rows — one per student. */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('period', now()->format('Y-m'));
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAiUsage::route('/'),
        ];
    }
}
