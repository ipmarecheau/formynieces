<?php

namespace App\Filament\Resources\SyllabusModules\Pages;

use App\Filament\Resources\SyllabusModules\SyllabusModuleResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditSyllabusModule extends EditRecord
{
    protected static string $resource = SyllabusModuleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
