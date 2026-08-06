<?php

namespace App\Filament\Resources\PracticeQuestions\Pages;

use App\Filament\Resources\PracticeQuestions\PracticeQuestionResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePracticeQuestion extends CreateRecord
{
    protected static string $resource = PracticeQuestionResource::class;

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return PracticeQuestionResource::packFormData($data);
    }
}
