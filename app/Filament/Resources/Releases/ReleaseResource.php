<?php

namespace App\Filament\Resources\Releases;

use App\Filament\Resources\Releases\Pages\CreateRelease;
use App\Filament\Resources\Releases\Pages\EditRelease;
use App\Filament\Resources\Releases\Pages\ListReleases;
use App\Filament\Resources\Releases\Schemas\ReleaseForm;
use App\Filament\Resources\Releases\Tables\ReleasesTable;
use App\Models\Release;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Filament\Forms\Components\TextInput;
use Filament\Actions\DeleteAction;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Auth;
use Filament\Notifications\Notification;
use App\Filament\Resources\Releases\RelationManagers\MusicRelationManager;

class ReleaseResource extends Resource
{
    protected static ?string $model = Release::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return ReleaseForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ReleasesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            MusicRelationManager::class,
        ];
    }
    public static function getPages(): array
    {
        return [
            'index' => ListReleases::route('/'),
            'create' => CreateRelease::route('/create'),
            'edit' => EditRelease::route('/{record}/edit'),
        ];
    }

    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                //
            ])

            ->actions([
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
