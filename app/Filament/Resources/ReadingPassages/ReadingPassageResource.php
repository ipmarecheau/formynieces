<?php

namespace App\Filament\Resources\ReadingPassages;

use App\Filament\Resources\ReadingPassages\Pages\CreateReadingPassage;
use App\Filament\Resources\ReadingPassages\Pages\EditReadingPassage;
use App\Filament\Resources\ReadingPassages\Pages\ListReadingPassages;
use App\Filament\Resources\ReadingPassages\Schemas\ReadingPassageForm;
use App\Filament\Resources\ReadingPassages\Tables\ReadingPassagesTable;
use App\Models\ReadingPassage;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ReadingPassageResource extends Resource
{
    protected static ?string $model = ReadingPassage::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBookOpen;

    protected static ?string $recordTitleAttribute = 'title';

    protected static ?string $navigationLabel = 'Reading Pool';

    public static function form(Schema $schema): Schema
    {
        return ReadingPassageForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ReadingPassagesTable::configure($table);
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
            'index' => ListReadingPassages::route('/'),
            'create' => CreateReadingPassage::route('/create'),
            'edit' => EditReadingPassage::route('/{record}/edit'),
        ];
    }
}
