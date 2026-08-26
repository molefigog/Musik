<?php

namespace App\Filament\Resources\Releases\Schemas;

use App\Models\Genre;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\Repeater\TableColumn;

class ReleaseForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([

            Section::make('Release Details')
                ->schema([
                    Grid::make(2)
                        ->schema([
                            TextInput::make('title')
                                ->required(),

                            FileUpload::make('art_cover')
                                ->image()
                                ->disk('public')
                                ->required()
                                ->directory('covers'),
                        ]),
                ])
                ->columnSpanFull(),

            Hidden::make('user_id')
                ->default(fn() => Auth::id()),

            // Repeater::make('music')
            //     ->relationship()

            //     ->reorderable()
            //     ->table([
            //         TableColumn::make('Name'),
            //         TableColumn::make('Amount'),
            //         TableColumn::make('Genre'),
            //         TableColumn::make('File Source'),
            //     ])
            //     ->schema([
            //         TextInput::make('title')
            //             ->required(),

            //         TextInput::make('price')
            //             ->numeric()
            //             ->required()
            //             ->columns([
            //                 'sm' => 3,

            //             ]),

            //         Select::make('genre_id')
            //             ->relationship('genre', 'title')
            //             ->required(),

            //         FileUpload::make('file_src')
            //             ->required()
            //             ->acceptedFileTypes(['audio/*'])
            //             ->disk('public')
            //             ->directory('music')
            //             ->columnSpanFull(),

            //     ])
            //     ->grid(2)
            //     ->columnSpanFull()
            //     ->collapsed()
            //     ->reorderableWithDragAndDrop(true),

            Section::make('Publish')
                ->schema([
                    Toggle::make('published')
                        ->default(false),
                ])->columnSpanFull(),

        ]);
    }
}
