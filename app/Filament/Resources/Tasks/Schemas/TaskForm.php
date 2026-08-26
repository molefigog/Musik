<?php

namespace App\Filament\Resources\Tasks\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class TaskForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('user_id')
                ->relationship('user', 'name')
                ->label('Customer')
                ->disabled()
                ->dehydrated(false),

            Select::make('service_type')
                ->options([
                    'beat' => 'Beat',
                    'recording' => 'Recording',
                    'artwork' => 'Artwork',
                ])
                ->required(),

            TextInput::make('title')
                ->required()
                ->maxLength(255),

            TextInput::make('amount')
                ->label('Task Price')
                ->numeric()
                ->prefix('M')
                ->required()
                ->minValue(0.01),

            Toggle::make('is_paid')
                ->label('Paid')
                ->default(false),

            Textarea::make('details')
                ->rows(4)
                ->columnSpanFull(),

            FileUpload::make('file_path')
                ->label('Final File')
                ->disk('public')
                ->directory('tasks')
                ->visibility('public')
                ->preserveFilenames()
                ->helperText('Uploading a file will mark this task as completed.')
                ->columnSpanFull(),

            FileUpload::make('preview_path')
                ->label('Preview File (Optional)')
                ->disk('public')
                ->directory('tasks')
                ->visibility('public')
                ->preserveFilenames()
                ->columnSpanFull(),

            Toggle::make('status')
                ->label('Completed')
                ->disabled()
                ->dehydrated(false),
        ]);
    }
}
