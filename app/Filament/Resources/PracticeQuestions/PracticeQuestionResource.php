<?php

namespace App\Filament\Resources\PracticeQuestions;

use App\Filament\Resources\PracticeQuestions\Pages\CreatePracticeQuestion;
use App\Filament\Resources\PracticeQuestions\Pages\EditPracticeQuestion;
use App\Filament\Resources\PracticeQuestions\Pages\ListPracticeQuestions;
use App\Filament\Resources\PracticeQuestions\Schemas\PracticeQuestionForm;
use App\Filament\Resources\PracticeQuestions\Tables\PracticeQuestionsTable;
use App\Models\PracticeQuestion;
use App\Models\SyllabusModule;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class PracticeQuestionResource extends Resource
{
    protected static ?string $model = PracticeQuestion::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'prompt';

    protected static ?string $navigationLabel = 'Question Bank';

    /**
     * Fold the four option fields and the chosen answer back into the stored
     * shape (an options array + a 0-based correct index), and copy the module's
     * subject/section/strand onto the question so it stays self-describing —
     * mirroring how the seeder and importer store questions.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public static function packFormData(array $data): array
    {
        $data['options'] = [
            (string) ($data['option_1'] ?? ''),
            (string) ($data['option_2'] ?? ''),
            (string) ($data['option_3'] ?? ''),
            (string) ($data['option_4'] ?? ''),
        ];
        $data['correct_index'] = (int) ($data['correct_option'] ?? 1) - 1;

        if ($module = SyllabusModule::find($data['module_id'] ?? null)) {
            $data['subject'] = $module->subject;
            $data['sea_section'] = $module->sea_section;
        }

        unset($data['option_1'], $data['option_2'], $data['option_3'], $data['option_4'], $data['correct_option']);

        return $data;
    }

    /**
     * Split the stored options array back into the four editable fields.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public static function unpackForForm(array $data): array
    {
        $options = array_values((array) ($data['options'] ?? []));
        for ($i = 0; $i < 4; $i++) {
            $data['option_'.($i + 1)] = $options[$i] ?? null;
        }
        $data['correct_option'] = (int) ($data['correct_index'] ?? 0) + 1;

        return $data;
    }

    public static function form(Schema $schema): Schema
    {
        return PracticeQuestionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PracticeQuestionsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPracticeQuestions::route('/'),
            'create' => CreatePracticeQuestion::route('/create'),
            'edit' => EditPracticeQuestion::route('/{record}/edit'),
        ];
    }
}
