<?php

namespace App\Filament\Resources\StudentProgress\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class StudentProgressForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('student_id')
                    ->relationship('student', 'name')
                    ->required(),
                Select::make('module_id')
                    ->relationship('module', 'id')
                    ->required(),
                TextInput::make('status')
                    ->required()
                    ->default('not_started'),
                TextInput::make('score')
                    ->numeric(),
                TextInput::make('previous_score')
                    ->numeric(),
            ]);
    }
}
