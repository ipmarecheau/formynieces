<?php

namespace App\Filament\Resources\SyllabusModules\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class SyllabusModuleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('subject')
                    ->required(),
                TextInput::make('topic')
                    ->required(),
                TextInput::make('sea_section')
                    ->required(),
                TextInput::make('sequence_order')
                    ->required()
                    ->numeric(),
                TextInput::make('pacing_week')
                    ->required()
                    ->numeric()
                    ->default(1),
                Textarea::make('description')
                    ->columnSpanFull(),
                Textarea::make('resources')
                    ->columnSpanFull(),
            ]);
    }
}
