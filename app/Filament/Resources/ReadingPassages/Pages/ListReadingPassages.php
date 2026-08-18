<?php

namespace App\Filament\Resources\ReadingPassages\Pages;

use App\Filament\Resources\ReadingPassages\ReadingPassageResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListReadingPassages extends ListRecords
{
    protected static string $resource = ReadingPassageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
