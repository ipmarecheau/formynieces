<?php

namespace App\Filament\Resources\Lessons\Pages;

use App\Filament\Resources\Lessons\LessonResource;
use Filament\Resources\Pages\CreateRecord;

class CreateLesson extends CreateRecord
{
    protected static string $resource = LessonResource::class;

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['blocks'] = LessonResource::flattenBlocks((array) ($data['blocks'] ?? []));

        return $data;
    }
}
