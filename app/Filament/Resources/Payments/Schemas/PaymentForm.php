<?php

namespace App\Filament\Resources\Payments\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class PaymentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('amount')
                    ->required()
                    ->numeric(),
                TextInput::make('status')
                    ->required(),
                TextInput::make('txn_id')
                    ->required(),
                TextInput::make('msisdn')
                    ->default(null),
                TextInput::make('conversation_id')
                    ->default(null),
                TextInput::make('type')
                    ->required(),
                Textarea::make('raw_response')
                    ->default(null)
                    ->columnSpanFull(),
                TextInput::make('statusCode')
                    ->default(null),
                Textarea::make('description')
                    ->default(null)
                    ->columnSpanFull(),
                TextInput::make('user_id')
                    ->required()
                    ->numeric(),
                TextInput::make('music_id')
                    ->numeric()
                    ->default(null),
                TextInput::make('service_id')
                    ->label('Task / Service ID')
                    ->numeric()
                    ->default(null),
            ]);
    }
}
