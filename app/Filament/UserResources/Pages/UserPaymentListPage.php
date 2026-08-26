<?php

namespace App\Filament\UserResources\Pages;

use App\Filament\UserResources\UserPaymentResource;
use Filament\Resources\Pages\ListRecords;

class UserPaymentListPage extends ListRecords
{
    protected static string $resource = UserPaymentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            //
        ];
    }
}
