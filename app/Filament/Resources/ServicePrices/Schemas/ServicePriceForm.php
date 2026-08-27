<?php

namespace App\Filament\Resources\ServicePrices\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ServicePriceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('service_type')
                    ->options(['beat' => 'Beat', 'recording' => 'Recording', 'artwork' => 'Artwork'])
                    ->required(),
                TextInput::make('amount')
                    ->required()
                    ->numeric(),
            ]);
    }
}
