<?php

namespace App\Filament\Resources\CapReviews;

use App\Filament\Resources\CapReviews\Pages\ListCapReviews;
use App\Filament\Resources\CapReviews\Tables\CapReviewsTable;
use App\Models\StudentJourney;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

/**
 * AC-04 — read-only admin list of students the Sunday rollover flagged for cap
 * review: their feasible pace needs more modules per week than their cap allows.
 */
class CapReviewResource extends Resource
{
    protected static ?string $model = StudentJourney::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = 'Cap Review';

    protected static ?string $modelLabel = 'cap review';

    protected static ?string $pluralModelLabel = 'cap reviews';

    public static function table(Table $table): Table
    {
        return CapReviewsTable::configure($table);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('cap_review_required', true);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCapReviews::route('/'),
        ];
    }
}
