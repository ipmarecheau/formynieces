<?php

namespace App\Filament\Resources\PracticeQuestions\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PracticeQuestionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Placement')
                    ->columns(2)
                    ->schema([
                        Select::make('module_id')
                            ->label('Syllabus module')
                            ->relationship('module', 'topic')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('difficulty')
                            ->label('Difficulty rung')
                            ->options([1 => 'Easy (rung 1)', 2 => 'Medium (rung 2)', 3 => 'Hard (rung 3)'])
                            ->required(),
                    ]),

                Section::make('Question')
                    ->columns(2)
                    ->schema([
                        Textarea::make('prompt')
                            ->helperText('Plain text, or HTML (e.g. an <img> figure) for richer questions.')
                            ->rows(3)
                            ->required()
                            ->columnSpanFull(),
                        TextInput::make('option_1')->label('Option 1')->required(),
                        TextInput::make('option_2')->label('Option 2')->required(),
                        TextInput::make('option_3')->label('Option 3')->required(),
                        TextInput::make('option_4')->label('Option 4')->required(),
                        Select::make('correct_option')
                            ->label('Correct answer')
                            ->options([1 => 'Option 1', 2 => 'Option 2', 3 => 'Option 3', 4 => 'Option 4'])
                            ->default(1)
                            ->required(),
                    ]),

                Section::make('Teaching')
                    ->schema([
                        Textarea::make('explanation')
                            ->helperText('Shown after answering — the worked solution.')
                            ->rows(3)
                            ->columnSpanFull(),
                        Textarea::make('hint')
                            ->rows(2)
                            ->columnSpanFull(),
                        Toggle::make('is_active')
                            ->default(true),
                    ]),
            ]);
    }
}
