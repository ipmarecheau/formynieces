<?php
namespace App\Filament\Resources\PastPaperQuestions\Pages;
use App\Filament\Resources\PastPaperQuestions\PastPaperQuestionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
class ListPastPaperQuestions extends ListRecords { protected static string $resource = PastPaperQuestionResource::class; protected function getHeaderActions(): array { return [CreateAction::make()]; } }
