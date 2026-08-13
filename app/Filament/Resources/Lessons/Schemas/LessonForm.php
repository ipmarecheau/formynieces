<?php

namespace App\Filament\Resources\Lessons\Schemas;

use Filament\Forms\Components\Builder;
use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

/**
 * The lesson authoring form (LE-05). A lesson is composed from typed interaction BLOCKS via a
 * Filament Builder — mix explanation, media and interactive steps in any order. Each block's
 * schema mirrors the runtime block shape the student renderer (LessonWalk) understands; the
 * Builder's {type,data} wrapper is flattened to that runtime shape on save (see LessonResource).
 */
class LessonForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Lesson')
                    ->columns(2)
                    ->schema([
                        Select::make('module_id')
                            ->label('Syllabus module')
                            ->relationship('module', 'topic')
                            ->searchable()
                            ->preload()
                            ->required(),
                        TextInput::make('title')
                            ->required(),
                        Toggle::make('is_published')
                            ->helperText('Only published lessons are served to students.')
                            ->default(true),
                    ]),

                Section::make('Content')
                    ->schema([
                        Builder::make('blocks')
                            ->label('Lesson blocks')
                            ->blocks(self::blocks())
                            ->blockNumbers(false)
                            ->collapsible()
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    /**
     * The interaction blocks an author can compose a lesson from. Explanation and media blocks
     * (heading, text, key, worked example, image) plus the inline multiple-choice check. Richer
     * interactive types (fill-in-the-blank, mark the words, match pairs, order the steps) are
     * added by their own scenarios.
     *
     * @return array<int, Block>
     */
    public static function blocks(): array
    {
        return [
            Block::make('heading')
                ->label('Heading')
                ->icon('heroicon-o-bars-3-bottom-left')
                ->schema([
                    TextInput::make('content')->label('Heading text')->required(),
                ]),

            Block::make('text')
                ->label('Explanation')
                ->icon('heroicon-o-bars-3')
                ->schema([
                    Textarea::make('content')->label('Explanation')->rows(3)->required(),
                ]),

            Block::make('key')
                ->label('Remember-this rule')
                ->icon('heroicon-o-key')
                ->schema([
                    Textarea::make('content')->label('The rule, in one line')->rows(2)->required(),
                ]),

            Block::make('example')
                ->label('Worked example')
                ->icon('heroicon-o-light-bulb')
                ->schema([
                    Textarea::make('content')->label('Set-up')->rows(2),
                    TagsInput::make('steps')->label('Steps')->placeholder('Add a step and press Enter'),
                ]),

            Block::make('visual')
                ->label('Image')
                ->icon('heroicon-o-photo')
                ->schema([
                    TextInput::make('content')->label('Image URL')->url()->required(),
                ]),

            Block::make('check')
                ->label('Inline check')
                ->icon('heroicon-o-question-mark-circle')
                ->schema([
                    TextInput::make('question')->required(),
                    TagsInput::make('options')->label('Options')->placeholder('Add an option and press Enter')->required(),
                    TextInput::make('answer')->label('Correct option index')
                        ->helperText('0 = the first option, 1 = the second, and so on.')
                        ->numeric()->default(0)->required(),
                    Textarea::make('explain')->label('Why it is right')->rows(2),
                ]),

            Block::make('fillblank')
                ->label('Fill in the blank')
                ->icon('heroicon-o-pencil-square')
                ->schema([
                    TextInput::make('prompt')->label('Sentence')->helperText('Use ___ where the blank goes.')->required(),
                    TextInput::make('answer')->label('Correct answer')->required(),
                    TagsInput::make('options')->label('Word bank (optional)')->placeholder('Add a word and press Enter')
                        ->helperText('If set, she taps a word; if empty, she types the answer.'),
                    Textarea::make('explain')->label('Why it is right')->rows(2),
                ]),

            Block::make('markwords')
                ->label('Mark the words')
                ->icon('heroicon-o-cursor-arrow-rays')
                ->schema([
                    TextInput::make('instruction')->label('Instruction')->placeholder('Tap the verb')->required(),
                    Textarea::make('text')->label('Sentence')->helperText('Wrap each target word in *asterisks*, e.g. The dog *runs* home.')->rows(2)->required(),
                    Textarea::make('explain')->label('Why')->rows(2),
                ]),

            Block::make('matchpairs')
                ->label('Match pairs')
                ->icon('heroicon-o-arrows-right-left')
                ->schema([
                    TextInput::make('instruction')->label('Instruction')->placeholder('Match each word to its meaning')->required(),
                    Repeater::make('pairs')
                        ->schema([
                            TextInput::make('left')->required(),
                            TextInput::make('right')->required(),
                        ])
                        ->columns(2)
                        ->minItems(2)
                        ->required(),
                ]),

            Block::make('ordersteps')
                ->label('Order the steps')
                ->icon('heroicon-o-bars-arrow-down')
                ->schema([
                    TextInput::make('instruction')->label('Instruction')->placeholder('Put the steps in order')->required(),
                    TagsInput::make('items')->label('Steps in the correct order')->placeholder('Add a step and press Enter')
                        ->helperText('Enter them in the RIGHT order; students see them shuffled.')->required(),
                ]),
        ];
    }
}
