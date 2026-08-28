<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Services\FirebaseService;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use BackedEnum;
use Filament\Support\Icons\Heroicon;


class TestTorchCommand extends Page
{
    protected string $view = 'filament.pages.test-torch-command';
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;


    protected static ?string $navigationLabel = 'Test Torch Command';

    protected static ?string $title = 'Test Torch Command';


    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('token')
                    ->label('FCM Token')
                    ->required()
                    ->helperText('Paste the device FCM token to test against.'),

                Select::make('state')
                    ->label('Torch State')
                    ->options([
                        'on' => 'On',
                        'off' => 'Off',
                    ])
                    ->default('on')
                    ->required(),
            ])
            ->statePath('data');
    }

    public function send(): void
    {
        $formData = $this->form->getState();

        $token = $formData['token'];
        $command = $formData['state'] === 'off' ? 'TORCH_OFF' : 'TORCH_ON';

        try {
            app(FirebaseService::class)->sendCommand([$token], $command);

            Notification::make()
                ->title("Sent {$command}")
                ->success()
                ->send();
        } catch (\Throwable $e) {
            Notification::make()
                ->title('Failed to send')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }
}