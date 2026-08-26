<?php

namespace App\Filament\Resources\Genres\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Forms\Components\FileUpload;

class GenreForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->required(),
                FileUpload::make('art_cover')
                    ->disk('public')
                    ->directory('covers')
                    ->image()
                    ->imageEditor()
                    ->columnSpanFull(),
            ]);
    }
}
