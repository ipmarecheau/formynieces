<?php

namespace App\Filament\Pages;

use App\Services\Content\ContentCoverageService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;

class ContentAudit extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentCheck;
    protected static ?string $navigationLabel = 'Content Audit';
    protected static ?string $title = 'Content Audit';
    protected string $view = 'filament.pages.content-audit';

    /** @var array<string, mixed> */
    public array $report = [];

    public function mount(ContentCoverageService $coverage): void
    {
        $this->report = $coverage->report();
    }

    protected function getHeaderActions(): array
    {
        return [Action::make('refresh')->label('Refresh audit')->icon(Heroicon::ArrowPath)->action(function (ContentCoverageService $coverage): void {
            $this->report = $coverage->report();
            Notification::make()->title('Audit refreshed')->success()->send();
        })];
    }
}
