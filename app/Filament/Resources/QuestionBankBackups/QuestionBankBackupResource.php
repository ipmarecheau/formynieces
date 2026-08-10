<?php

namespace App\Filament\Resources\QuestionBankBackups;

use App\Filament\Resources\QuestionBankBackups\Pages\ListQuestionBankBackups;
use App\Filament\Resources\QuestionBankBackups\Tables\QuestionBankBackupsTable;
use App\Models\QuestionBankBackup;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class QuestionBankBackupResource extends Resource
{
    protected static ?string $model = QuestionBankBackup::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArchiveBox;

    protected static ?string $navigationLabel = 'Question Bank Backups';

    protected static ?string $recordTitleAttribute = 'reason';

    public static function canCreate(): bool
    {
        return false; // backups are taken by the daily job / the bank's own actions
    }

    public static function table(Table $table): Table
    {
        return QuestionBankBackupsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListQuestionBankBackups::route('/'),
        ];
    }
}
