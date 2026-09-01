<?php

namespace App\Filament\UserResources;

use App\Models\User;
use App\Filament\UserResources\Pages\UserProfileListPage;
use App\Filament\UserResources\Pages\UserProfileEditPage;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
use Filament\Actions\EditAction;

class UserProfileResource extends Resource
{
    protected static ?string $model = User::class;
    protected static ?string $navigationLabel = 'Profile';
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;
    protected static ?int $navigationSort = 4;
    protected static bool $shouldRegisterNavigation = false;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Personal Information')
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('email')
                            ->email()
                            ->required()
                            ->unique(table: User::class, column: 'email', ignoreRecord: true),
                        TextInput::make('tel')
                            ->tel()
                            ->maxLength(20),
                    ]),
                Section::make('Account')
                    ->schema([
                        TextInput::make('password')
                            ->password()
                            ->dehydrated(fn($state) => filled($state))
                            ->required(fn(string $context): bool => $context === 'create'),
                    ]),
                Section::make('Wallet & Balance')
                    ->schema([
                        TextInput::make('wallet')
                            ->numeric()
                            ->disabled(),
                        TextInput::make('balance')
                            ->numeric()
                            ->disabled(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name'),
                TextColumn::make('email'),
                TextColumn::make('tel'),
                TextColumn::make('wallet'),
                TextColumn::make('balance'),
            ])
            ->filters([
                //
            ])
            ->actions([
                EditAction::make(),
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

    public static function getPages(): array
    {
        return [
            'index' => UserProfileListPage::route('/'),
            'edit' => UserProfileEditPage::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('id', Auth::id());
    }
}
