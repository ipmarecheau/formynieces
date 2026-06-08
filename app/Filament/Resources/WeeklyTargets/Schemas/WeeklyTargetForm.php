<?php

namespace App\Filament\Resources\WeeklyTargets\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class WeeklyTargetForm
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
                DatePicker::make('week_start_date')
                    ->required(),
                Toggle::make('is_completed')
                    ->required(),
            ]);
    }
}
