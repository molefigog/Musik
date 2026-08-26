<?php

namespace App\Filament\Resources\Releases\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Filament\Notifications\Notification;

class ReleasesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->searchable(),

                ImageColumn::make('art_cover')->disk('public'),

                IconColumn::make('published')
                    ->boolean(),

                TextColumn::make('user.name')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])

            ->filters([
                //
            ])

            ->recordActions([
                EditAction::make(),

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
            ])

            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
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
                ]),
            ]);
    }
}
