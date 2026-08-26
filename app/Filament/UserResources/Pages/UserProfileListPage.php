<?php

namespace App\Filament\UserResources\Pages;

use App\Filament\UserResources\UserProfileResource;
use Filament\Resources\Pages\ListRecords;

class UserProfileListPage extends ListRecords
{
    protected static string $resource = UserProfileResource::class;

    protected function getHeaderActions(): array
    {
        return [
            //
        ];
    }
}
