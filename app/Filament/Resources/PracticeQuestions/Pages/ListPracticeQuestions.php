<?php

namespace App\Filament\Resources\PracticeQuestions\Pages;

use App\Filament\Resources\PracticeQuestions\PracticeQuestionResource;
use App\Services\QuestionBank\MoodleQuestionExporter;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ListPracticeQuestions extends ListRecords
{
    protected static string $resource = PracticeQuestionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
            Action::make('export')
                ->label('Export to Moodle XML')
                ->icon(Heroicon::ArrowDownTray)
                ->color('gray')
                ->action(fn (): StreamedResponse => response()->streamDownload(
                    fn () => print (app(MoodleQuestionExporter::class)->export()),
                    'sea_question_bank_'.now()->format('Y-m-d').'.xml',
                    ['Content-Type' => 'application/xml'],
                )),
        ];
    }
}
