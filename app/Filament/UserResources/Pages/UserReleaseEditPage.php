<?php

namespace App\Filament\UserResources\Pages;

use App\Filament\UserResources\UserReleaseResource;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Filament\Notifications\Notification;
use App\Models\Music;
use App\Models\Genre;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Actions\Action;
use Filament\Forms\Components\ViewField;

class UserReleaseEditPage extends EditRecord
{
    protected static string $resource = UserReleaseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->form([
                    TextInput::make('password')
                        ->label('Confirm Password')
                        ->password()
                        ->required(),
                ])
                ->before(function (array $data) {
                    if (! Hash::check($data['password'], Auth::user()->password)) {

                        Notification::make()
                            ->title('Incorrect Password')
                            ->body('The password you entered is incorrect. Deletion was cancelled.')
                            ->danger()
                            ->send();

                        throw ValidationException::withMessages([
                            'password' => 'Incorrect password.',
                        ]);
                    }
                })
                ->successNotificationTitle('Release deleted successfully'),

            Action::make('addTrack')
                ->label('Add Track')
                ->icon('heroicon-o-plus')
                ->modalHeading('Add Track')
                ->modalWidth('xl')
                ->form([
                    TextInput::make('title')
                        ->required(),

                    TextInput::make('price')
                        ->numeric()
                        ->required(),

                    Select::make('genre_id')
                        ->label('Genre')
                        ->options(Genre::pluck('title', 'id'))
                        ->searchable()
                        ->required(),

                    FileUpload::make('file_src')
                        ->disk('public')
                        ->directory('music')
                        ->acceptedFileTypes(['audio/*'])
                        ->storeFileNamesIn('file_name')
                        ->moveFiles()
                        ->required(),

                ])

                ->action(function (array $data) {

                    $music = Music::create([
                        'release_id' => $this->record->id,
                        'title' => $data['title'],
                        'price' => $data['price'],
                        'genre_id' => $data['genre_id'],
                        'file_src' => $data['file_src'],
                        'file_name' => $data['file_name'] ?? null,
                    ]);
                    return redirect(\App\Filament\UserResources\Pages\WaveSurferPage::getUrl([
                        'music' => $music->id
                    ]));
                    // Notification::make()
                    //     ->title('Track added successfully')
                    //     ->success()
                    //     ->send();
                }),
        ];
    }
}
