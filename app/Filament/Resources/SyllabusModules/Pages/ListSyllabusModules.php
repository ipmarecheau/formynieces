<?php

namespace App\Filament\Resources\SyllabusModules\Pages;

use App\Filament\Resources\SyllabusModules\SyllabusModuleResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSyllabusModules extends ListRecords
{
    protected static string $resource = SyllabusModuleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
