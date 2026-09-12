<?php

namespace App\Filament\Resources\PastPaperQuestions;

use App\Models\PastPaperQuestion;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Actions\EditAction;
use Filament\Support\Icons\Heroicon;
use BackedEnum;
use App\Filament\Resources\PastPaperQuestions\Pages\{CreatePastPaperQuestion, EditPastPaperQuestion, ListPastPaperQuestions};

class PastPaperQuestionResource extends Resource
{
    protected static ?string $model = PastPaperQuestion::class;
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedQuestionMarkCircle;
    protected static ?string $navigationLabel = 'Paper Questions';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('past_paper_id')->relationship('paper', 'title')->searchable()->preload()->required(),
            Select::make('syllabus_module_id')->relationship('module', 'topic')->searchable()->preload()->required(),
            TextInput::make('number')->numeric()->required(),
            Select::make('item_type')->options(['mcq' => 'Multiple choice', 'numeric' => 'Numeric', 'extended' => 'Extended response'])->required(),
            Textarea::make('prompt')->required()->rows(3),
            Textarea::make('options')->label('Options JSON')->helperText('["3","4","5","6"]'),
            Textarea::make('illustration_svg')->label('Illustration SVG')->rows(6)->helperText('Optional safe SVG for diagrams, tables, or geometry.'),
            TextInput::make('correct_answer')->required(),
            TextInput::make('marks')->numeric()->required()->default(1),
            TextInput::make('objective'),
            Select::make('provenance')->options(['real' => 'Real', 'generated' => 'AI-drafted variant'])->required(),
            Select::make('qc_status')->options(['unapproved' => 'Awaiting QC', 'approved' => 'Approved', 'discarded' => 'Discarded'])->required(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('paper.title')->label('Paper')->searchable(), TextColumn::make('number')->sortable(),
            TextColumn::make('prompt')->limit(60), TextColumn::make('provenance')->badge(), TextColumn::make('qc_status')->badge(),
        ])->recordActions([EditAction::make()]);
    }

    public static function getPages(): array
    { return ['index' => ListPastPaperQuestions::route('/'), 'create' => CreatePastPaperQuestion::route('/create'), 'edit' => EditPastPaperQuestion::route('/{record}/edit')]; }
}
