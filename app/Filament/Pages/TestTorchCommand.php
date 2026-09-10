<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Models\User;
use App\Models\FcmToken;
use App\Services\FirebaseService;
use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;
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
                Select::make('user_ids')
                    ->label('Users')
                    ->multiple()
                    ->searchable()
                    ->preload()
                    ->helperText('Only users with at least one registered device (FCM token) are listed.')
                    ->options(
                        fn() => User::whereHas('fcmTokens')
                            ->get()
                            ->mapWithKeys(fn(User $user) => [
                                $user->id => "{$user->name} ({$user->email})",
                            ])
                            ->toArray()
                    )
                    ->getSearchResultsUsing(
                        fn(string $search) => User::whereHas('fcmTokens')
                            ->where(function ($query) use ($search) {
                                $query->where('name', 'like', "%{$search}%")
                                    ->orWhere('email', 'like', "%{$search}%");
                            })
                            ->limit(50)
                            ->get()
                            ->mapWithKeys(fn(User $user) => [
                                $user->id => "{$user->name} ({$user->email})",
                            ])
                            ->toArray()
                    )
                    ->getOptionLabelsUsing(
                        fn(array $values) => User::whereIn('id', $values)
                            ->pluck('name', 'id')
                            ->toArray()
                    )
                    ->required(),

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

        $userIds = $formData['user_ids'] ?? [];
        $command = $formData['state'] === 'off' ? 'TORCH_OFF' : 'TORCH_ON';

        $tokens = FcmToken::whereIn('user_id', $userIds)
            ->pluck('token')
            ->filter()
            ->unique()
            ->values()
            ->toArray();

        if (empty($tokens)) {
            Notification::make()
                ->title('No devices found')
                ->body('None of the selected users have a registered device token.')
                ->warning()
                ->send();

            return;
        }

        try {
            app(FirebaseService::class)->sendCommand($tokens, $command);

            Notification::make()
                ->title("Sent {$command}")
                ->body(count($tokens) . ' device(s) notified.')
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
