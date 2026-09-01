<?php

namespace App\Filament\UserResources;

use App\Models\Release;
use App\Filament\UserResources\Pages\UserReleaseListPage;
use App\Filament\UserResources\Pages\UserReleaseCreatePage;
use App\Filament\UserResources\Pages\UserReleaseEditPage;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\FileUpload;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Actions\DeleteAction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Hash;
use Filament\Notifications\Notification;
use Filament\Forms\Components\Hidden;
use App\Filament\UserResources\MusicRelationManager;


class UserReleaseResource extends Resource
{
    protected static ?string $model = Release::class;
    protected static ?string $navigationLabel = 'Releases';
    protected static string|BackedEnum|null $navigationIcon = Heroicon::PlayCircle;
    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                TextInput::make('title')
                    ->required()
                    ->maxLength(255),
                FileUpload::make('art_cover')
                    ->image()
                    ->disk('public')
                    ->required()
                    ->directory('covers'),
                Toggle::make('published')
                    ->default(false),
                Hidden::make('user_id')
                    ->default(fn() => Auth::id()),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->searchable()
                    ->sortable(),
                ImageColumn::make('art_cover')->disk('public'),


                ToggleColumn::make('published')
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
                \Filament\Actions\EditAction::make()->label('Edit, Add Music'),
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
            MusicRelationManager::class,
        ];
    }
    public static function getNavigationBadge(): ?string
    {
        return static::getEloquentQuery()->count();
    }
    public static function getPages(): array
    {
        return [
            'index' => UserReleaseListPage::route('/'),
            'create' => UserReleaseCreatePage::route('/create'),
            'edit' => UserReleaseEditPage::route('/{record}/edit'),

        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('user_id', Auth::id());
    }
}