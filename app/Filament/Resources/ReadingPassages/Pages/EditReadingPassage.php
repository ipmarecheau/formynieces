<?php

namespace App\Filament\Resources\ReadingPassages\Pages;

use App\Filament\Resources\ReadingPassages\ReadingPassageResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditReadingPassage extends EditRecord
{
    protected static string $resource = ReadingPassageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
