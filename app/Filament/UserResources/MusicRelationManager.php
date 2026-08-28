<?php

namespace App\Filament\UserResources;

use App\Models\Genre;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Auth;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\ToggleColumn;

class MusicRelationManager extends RelationManager
{
    protected static string $relationship = 'music';

    protected static ?string $title = 'Tracks';


    public function table(Table $table): Table
    {
        return $table
            ->poll('10s')
            ->columns([

                TextColumn::make('title')
                    ->searchable(),
                TextColumn::make('genre.title'),
                TextColumn::make('price'),
                TextColumn::make('duration'),
                TextColumn::make('size'),
                ToggleColumn::make('is_published')->label('Published'),

            ])

            ->recordActions([
                // EditAction::make(),
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
                    }),
            ]);
    }
}