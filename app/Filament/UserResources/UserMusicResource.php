<?php

namespace App\Filament\UserResources;

use App\Models\Music;
use App\Filament\UserResources\Pages\UserMusicListPage;
use App\Filament\UserResources\Pages\UserMusicCreatePage;
use App\Filament\UserResources\Pages\UserMusicEditPage;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\FileUpload;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Actions\DeleteAction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Hash;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\ToggleColumn;

class UserMusicResource extends Resource
{
    protected static ?string $model = Music::class;
    protected static ?string $navigationLabel = 'Music';
    protected static string|BackedEnum|null $navigationIcon = Heroicon::MusicalNote;
    protected static ?int $navigationSort = 2;
    public static function getGloballySearchableAttributes(): array
    {
        return ['title', 'price', 'release.title', 'genre.title'];
    }
    // public static function form(Schema $schema): Schema
    // {
    //     return $schema
    //         ->schema([
    //             TextInput::make('title')
    //                 ->required()
    //                 ->maxLength(255),
    //             TextInput::make('price')
    //                 ->numeric()
    //                 ->minValue(0),
    //             TextInput::make('duration')
    //                 ->numeric()
    //                 ->minValue(0),
    //             Select::make('release_id')
    //                 ->relationship('release', 'title')
    //                 ->required()
    //                 ->preload(),
    //             Select::make('genre_id')
    //                 ->relationship('genre', 'title')
    //                 ->preload(),
    //             FileUpload::make('file_src')
    //                 ->acceptedFileTypes(['audio/mpeg', 'audio/wav', 'audio/ogg'])
    //                 ->directory('music')
    //                 ->required(),
    //         ]);
    // }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('price')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('release.title')
                    ->label('Release')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('genre.title')
                    ->label('Genre')
                    ->sortable(),
                TextColumn::make('duration')
                    ->numeric(),

                ToggleColumn::make('is_published')
                    ->label('Published')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                // \Filament\Actions\EditAction::make(),
                DeleteAction::make()
                    ->form([
                        TextInput::make('password')
                            ->label('Confirm Password')
                            ->password()
                            ->required(),
                    ])
                    ->before(function (array $data) {
                        if (!Hash::check($data['password'], Auth::user()->password)) {
                            Notification::make()
                                ->title('Incorrect Password')
                                ->body('The password you entered is incorrect.')
                                ->danger()
                                ->send();
                            throw ValidationException::withMessages([
                                'password' => 'Incorrect password.',
                            ]);
                        }
                    }),
            ])
            ->bulkActions([
                //
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }
    public static function getNavigationBadge(): ?string
    {
        return static::getEloquentQuery()->count();
    }
    public static function getPages(): array
    {
        return [
            'index' => UserMusicListPage::route('/'),
            'create' => UserMusicCreatePage::route('/create'),
            'edit' => UserMusicEditPage::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        // Get music from releases that belong to the current user
        return parent::getEloquentQuery()
            ->whereHas('release', function (Builder $query) {
                $query->where('user_id', Auth::id());
            });
    }
}