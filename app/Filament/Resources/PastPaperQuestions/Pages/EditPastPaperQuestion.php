<?php
namespace App\Filament\Resources\PastPaperQuestions\Pages;
use App\Filament\Resources\PastPaperQuestions\PastPaperQuestionResource;
use Filament\Resources\Pages\EditRecord;
class EditPastPaperQuestion extends EditRecord { protected static string $resource = PastPaperQuestionResource::class; protected function mutateFormDataBeforeFill(array $data): array { $data['options'] = json_encode($data['options'] ?? []); return $data; } protected function mutateFormDataBeforeSave(array $data): array { $data['options'] = json_decode((string)($data['options'] ?? '[]'), true) ?: []; return $data; } }
