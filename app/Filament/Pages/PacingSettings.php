<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Schema;

/**
 * @property-read Schema $form
 */
class PacingSettings extends Page
{
    protected string $view = 'filament.pages.pacing-settings';

    protected static ?string $navigationLabel = 'Pacing Settings';

    protected static ?string $title = 'Pacing Settings';

    /**
     * @var array<string, mixed> | null
     */
    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'weekly_module_cap' => Setting::get('weekly_module_cap', '5'),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Form::make([
                    TextInput::make('weekly_module_cap')
                        ->label('Global weekly module cap')
                        ->numeric()
                        ->minValue(1)
                        ->maxValue(20)
                        ->required(),
                ])
                    ->livewireSubmitHandler('save')
                    ->footer([
                        Actions::make([
                            Action::make('save')
                                ->submit('save')
                                ->keyBindings(['mod+s']),
                        ]),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        Setting::put('weekly_module_cap', (string) $data['weekly_module_cap']);

        Notification::make()
            ->success()
            ->title('Pacing settings saved')
            ->send();
    }
}
