<?php

namespace App\Filament\Resources\CapReviews\Tables;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CapReviewsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('student.name')
                    ->label('Student')
                    ->searchable(),
                TextColumn::make('required_pace')
                    ->label('Required pace (modules/week)')
                    ->sortable(),
                TextColumn::make('exam_date')
                    ->label('Exam date')
                    ->date()
                    ->sortable(),
            ])
            ->defaultSort('required_pace', 'desc');
    }
}
