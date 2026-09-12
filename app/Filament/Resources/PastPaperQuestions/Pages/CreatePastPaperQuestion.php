<?php
namespace App\Filament\Resources\PastPaperQuestions\Pages;
use App\Filament\Resources\PastPaperQuestions\PastPaperQuestionResource;
use Filament\Resources\Pages\CreateRecord;
class CreatePastPaperQuestion extends CreateRecord { protected static string $resource = PastPaperQuestionResource::class; protected function mutateFormDataBeforeCreate(array $data): array { $data['options'] = json_decode((string)($data['options'] ?? '[]'), true) ?: []; return $data; } }
