<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('email')
                    ->label('Email address')
                    ->email()
                    ->required(),
                DateTimePicker::make('email_verified_at'),
                TextInput::make('password')
                    ->password()
                    ->required(fn (string $operation): bool => $operation === 'create')
                    ->dehydrated(fn ($state): bool => filled($state))
                    ->maxLength(255),
                TextInput::make('role')
                    ->required()
                    ->default('student'),
                Select::make('parent_id')
                    ->relationship('parent', 'name'),
                TextInput::make('weekly_module_cap_override')
                    ->label('Weekly module cap override')
                    ->helperText('Leave blank to use the global cap. Set a number to override this student\'s weekly module cap.')
                    ->numeric()
                    ->minValue(1)
                    ->maxValue(20)
                    ->nullable(),
            ]);
    }
}
