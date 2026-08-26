<?php

namespace App\Filament\Resources\Lessons\Pages;

use App\Filament\Resources\Lessons\LessonResource;
use App\Models\Lesson;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;

class EditLesson extends EditRecord
{
    protected static string $resource = LessonResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('preview')
                ->label('Preview as student')
                ->icon(Heroicon::Play)
                ->color('primary')
                ->url(fn (Lesson $record): string => route('admin.lessons.preview', $record->module_id))
                ->openUrlInNewTab(),
            Action::make('previewReteach')
                ->label('Preview re-teach')
                ->icon(Heroicon::ArrowPath)
                ->color('warning')
                ->url(fn (Lesson $record): string => route('admin.lessons.preview-reteach', $record->module_id))
                ->openUrlInNewTab(),
            DeleteAction::make(),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['blocks'] = LessonResource::nestBlocks((array) ($data['blocks'] ?? []));

        return $data;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['blocks'] = LessonResource::flattenBlocks((array) ($data['blocks'] ?? []));

        return $data;
    }
}
