<?php

namespace App\Filament\Resources\SyllabusModules;

use App\Filament\Resources\SyllabusModules\Pages\CreateSyllabusModule;
use App\Filament\Resources\SyllabusModules\Pages\EditSyllabusModule;
use App\Filament\Resources\SyllabusModules\Pages\ListSyllabusModules;
use App\Models\SyllabusModule;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Repeater;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;

class SyllabusModuleResource extends Resource
{
    protected static ?string $model = SyllabusModule::class;
    protected static ?string $navigationLabel = 'Syllabus Modules';
    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Module Details')
                ->schema([
                    Grid::make(2)->schema([
                        Select::make('subject')
                            ->options([
                                'Math'                  => 'Math',
                                'English Editing'       => 'English Editing',
                                'English Comprehension' => 'English Comprehension',
                            ])
                            ->required(),
                        Select::make('sea_section')
                            ->label('SEA Section')
                            ->options([
                                'Section I'   => 'Section I',
                                'Section II'  => 'Section II',
                                'Section III' => 'Section III',
                            ])
                            ->required(),
                        TextInput::make('topic')
                            ->required()
                            ->maxLength(255),
                        Grid::make(2)->schema([
                            TextInput::make('sequence_order')
                                ->numeric()
                                ->required(),
                            TextInput::make('pacing_week')
                                ->numeric()
                                ->required()
                                ->minValue(1)
                                ->maxValue(30)
                                ->helperText('Teaching week (1-30)'),
                        ]),
                    ]),
                ]),

            Section::make('Content & Resources')
                ->schema([
                    Textarea::make('description')
                        ->rows(4)
                        ->helperText('Explain what this topic tests.'),
                    Repeater::make('resources')
                        ->schema([
                            TextInput::make('title')
                                ->required()
                                ->placeholder('e.g. Khan Academy — Fractions'),
                            TextInput::make('url')
                                ->url()
                                ->placeholder('https://...')
                                ->helperText('Leave blank for offline resources.'),
                        ])
                        ->columns(2)
                        ->helperText('Add vetted resources for this topic.'),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('pacing_week')
                    ->label('Week')
                    ->sortable()
                    ->badge()
                    ->color('primary'),
                TextColumn::make('subject')
                    ->sortable()
                    ->badge()
                    ->color(fn($state) => match($state) {
                        'Math'                  => 'success',
                        'English Editing'       => 'danger',
                        'English Comprehension' => 'warning',
                        default                 => 'gray',
                    }),
                TextColumn::make('sea_section')
                    ->label('Section')
                    ->sortable(),
                TextColumn::make('topic')
                    ->searchable()
                    ->wrap()
                    ->limit(60),
                IconColumn::make('description')
                    ->label('Has Description')
                    ->boolean()
                    ->getStateUsing(fn($record) => !empty($record->description)),
                IconColumn::make('resources')
                    ->label('Has Resources')
                    ->boolean()
                    ->getStateUsing(fn($record) => !empty($record->resources)),
            ])
            ->defaultSort('sequence_order')
            ->filters([
                SelectFilter::make('subject')
                    ->options([
                        'Math'                  => 'Math',
                        'English Editing'       => 'English Editing',
                        'English Comprehension' => 'English Comprehension',
                    ]),
                SelectFilter::make('sea_section')
                    ->options([
                        'Section I'   => 'Section I',
                        'Section II'  => 'Section II',
                        'Section III' => 'Section III',
                    ]),
            ])
            ->actions([
                EditAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListSyllabusModules::route('/'),
            'create' => CreateSyllabusModule::route('/create'),
            'edit'   => EditSyllabusModule::route('/{record}/edit'),
        ];
    }
}