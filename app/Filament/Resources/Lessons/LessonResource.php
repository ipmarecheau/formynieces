<?php

namespace App\Filament\Resources\Lessons;

use App\Filament\Resources\Lessons\Pages\CreateLesson;
use App\Filament\Resources\Lessons\Pages\EditLesson;
use App\Filament\Resources\Lessons\Pages\ListLessons;
use App\Filament\Resources\Lessons\Schemas\LessonForm;
use App\Filament\Resources\Lessons\Tables\LessonsTable;
use App\Models\Lesson;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class LessonResource extends Resource
{
    protected static ?string $model = Lesson::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBookOpen;

    protected static ?string $recordTitleAttribute = 'title';

    protected static ?string $navigationLabel = 'Lessons';

    /**
     * Flatten Filament Builder items ({type, data:{...}}) into the runtime block shape the
     * student renderer understands ({type, ...data}). Called on save so stored lessons match
     * what LessonWalk reads.
     *
     * @param  array<int, array<string, mixed>>  $builderBlocks
     * @return array<int, array<string, mixed>>
     */
    public static function flattenBlocks(array $builderBlocks): array
    {
        return array_values(array_map(function (array $item): array {
            $data = (array) ($item['data'] ?? []);

            return array_merge(['type' => $item['type'] ?? 'text'], $data);
        }, $builderBlocks));
    }

    /**
     * Nest runtime blocks ({type, ...}) back into the Builder shape ({type, data:{...}}) so an
     * existing lesson loads into the authoring form for editing.
     *
     * @param  array<int, array<string, mixed>>  $flatBlocks
     * @return array<int, array<string, mixed>>
     */
    public static function nestBlocks(array $flatBlocks): array
    {
        return array_values(array_map(function (array $block): array {
            $type = $block['type'] ?? 'text';
            unset($block['type']);

            return ['type' => $type, 'data' => $block];
        }, $flatBlocks));
    }

    public static function form(Schema $schema): Schema
    {
        return LessonForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LessonsTable::configure($table);
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
            'index' => ListLessons::route('/'),
            'create' => CreateLesson::route('/create'),
            'edit' => EditLesson::route('/{record}/edit'),
        ];
    }
}
