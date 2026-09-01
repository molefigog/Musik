<?php

namespace App\Filament\Resources\ServicePrices\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextInputColumn;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Filament\Notifications\Notification;
use Filament\Forms\Components\TextInput;

class ServicePricesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('service_type')
                    ->badge(),
                TextInputColumn::make('amount')

            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
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
