<?php

namespace App\Filament\Resources\PastPapers;

use App\Filament\Resources\PastPapers\Pages\CreatePastPaper;
use App\Filament\Resources\PastPapers\Pages\EditPastPaper;
use App\Filament\Resources\PastPapers\Pages\ListPastPapers;
use App\Models\PastPaper;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Support\Icons\Heroicon;
use BackedEnum;

class PastPaperResource extends Resource
{
    protected static ?string $model = PastPaper::class;
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;
    protected static ?string $navigationLabel = 'Past Papers';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('title')->required()->maxLength(160),
            Select::make('subject')->options(['Math' => 'Math', 'ELA' => 'ELA'])->required(),
            Select::make('provenance')->options(['real' => 'Real paper', 'generated' => 'AI-drafted variant'])->required(),
            TextInput::make('source_ref')->label('Source reference')->maxLength(190),
            Toggle::make('is_published')->label('Available for composition')->default(false),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('title')->searchable()->sortable(),
            TextColumn::make('subject')->badge(),
            TextColumn::make('provenance')->label('Source'),
            TextColumn::make('questions_count')->counts('questions')->label('Questions'),
            IconColumn::make('is_published')->boolean()->label('Published'),
        ])->recordActions([
            \Filament\Actions\EditAction::make(),
        ]);
    }

    public static function getPages(): array
    {
        return ['index' => ListPastPapers::route('/'), 'create' => CreatePastPaper::route('/create'), 'edit' => EditPastPaper::route('/{record}/edit')];
    }
}
