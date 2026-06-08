<?php

namespace App\Filament\Resources\WeeklyTargets\Pages;

use App\Filament\Resources\WeeklyTargets\WeeklyTargetResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditWeeklyTarget extends EditRecord
{
    protected static string $resource = WeeklyTargetResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
