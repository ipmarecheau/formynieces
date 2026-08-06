<?php

namespace App\Filament\Resources\PracticeQuestions\Pages;

use App\Filament\Resources\PracticeQuestions\PracticeQuestionResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPracticeQuestion extends EditRecord
{
    protected static string $resource = PracticeQuestionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        return PracticeQuestionResource::unpackForForm($data);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        return PracticeQuestionResource::packFormData($data);
    }
}
