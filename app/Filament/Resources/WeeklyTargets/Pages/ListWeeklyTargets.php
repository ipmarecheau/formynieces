<?php

namespace App\Filament\Resources\WeeklyTargets\Pages;

use App\Filament\Resources\WeeklyTargets\WeeklyTargetResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListWeeklyTargets extends ListRecords
{
    protected static string $resource = WeeklyTargetResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
