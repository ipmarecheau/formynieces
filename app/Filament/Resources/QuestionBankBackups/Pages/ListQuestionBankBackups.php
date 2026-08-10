<?php

namespace App\Filament\Resources\QuestionBankBackups\Pages;

use App\Filament\Resources\QuestionBankBackups\QuestionBankBackupResource;
use Filament\Resources\Pages\ListRecords;

class ListQuestionBankBackups extends ListRecords
{
    protected static string $resource = QuestionBankBackupResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
