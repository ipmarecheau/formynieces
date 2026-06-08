<?php

namespace App\Filament\Resources\StudentProgress;

use App\Filament\Resources\StudentProgress\Pages\CreateStudentProgress;
use App\Filament\Resources\StudentProgress\Pages\EditStudentProgress;
use App\Filament\Resources\StudentProgress\Pages\ListStudentProgress;
use App\Filament\Resources\StudentProgress\Schemas\StudentProgressForm;
use App\Filament\Resources\StudentProgress\Tables\StudentProgressTable;
use App\Models\StudentProgress;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class StudentProgressResource extends Resource
{
    protected static ?string $model = StudentProgress::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'status';

    public static function form(Schema $schema): Schema
    {
        return StudentProgressForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return StudentProgressTable::configure($table);
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
            'index' => ListStudentProgress::route('/'),
            'create' => CreateStudentProgress::route('/create'),
            'edit' => EditStudentProgress::route('/{record}/edit'),
        ];
    }
}
