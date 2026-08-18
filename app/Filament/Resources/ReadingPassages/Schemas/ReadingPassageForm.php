<?php

namespace App\Filament\Resources\ReadingPassages\Schemas;

use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

/**
 * The daily reading-pool authoring form (DR-06). An admin stocks a passage with
 * its reading level and comprehension questions in advance; passages become
 * available to be served on future mornings. The questions Repeater mirrors the
 * runtime shape DailyReadingService serves and ComprehensionScorer grades:
 * {prompt, type: mc|written, options[], correct_index}.
 */
class ReadingPassageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Passage')
                    ->columns(2)
                    ->schema([
                        TextInput::make('title')
                            ->required()
                            ->columnSpanFull(),
                        Select::make('reading_level')
                            ->label('Reading level')
                            ->options(array_combine(range(1, 6), range(1, 6)))
                            ->helperText('The level this passage is keyed to; served to students at that reading level.')
                            ->required(),
                        Toggle::make('is_active')
                            ->label('Available to serve')
                            ->default(true)
                            ->helperText('Only active passages are served on future mornings.'),
                        Textarea::make('body')
                            ->label('Passage text')
                            ->rows(12)
                            ->required()
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (Set $set, ?string $state) => $set(
                                'word_count',
                                $state ? str_word_count($state) : 0,
                            ))
                            ->columnSpanFull(),
                        TextInput::make('word_count')
                            ->label('Word count')
                            ->numeric()
                            ->helperText('Auto-counted from the passage text; used to size the ride-to-school ritual and pace.')
                            ->required(),
                    ]),
                Section::make('Comprehension questions')
                    ->schema([
                        Repeater::make('questions')
                            ->hiddenLabel()
                            ->addActionLabel('Add a question')
                            ->schema([
                                Textarea::make('prompt')
                                    ->required()
                                    ->rows(2)
                                    ->columnSpanFull(),
                                Select::make('type')
                                    ->options([
                                        'mc' => 'Multiple choice',
                                        'written' => 'Written answer',
                                    ])
                                    ->default('mc')
                                    ->required()
                                    ->live(),
                                TagsInput::make('options')
                                    ->label('Options')
                                    ->helperText('Add each answer choice; their order is the 0-based index.')
                                    ->visible(fn (Get $get): bool => $get('type') === 'mc')
                                    ->columnSpanFull(),
                                TextInput::make('correct_index')
                                    ->label('Correct option (0-based index)')
                                    ->numeric()
                                    ->minValue(0)
                                    ->required()
                                    ->visible(fn (Get $get): bool => $get('type') === 'mc'),
                            ])
                            ->columns(2)
                            ->itemLabel(fn (array $state): ?string => isset($state['prompt'])
                                ? Str::limit($state['prompt'], 60)
                                : null),
                    ]),
            ]);
    }
}
