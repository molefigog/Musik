<?php

namespace App\Filament\UserResources\Pages;

use App\Filament\UserResources\UserReleaseResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class UserReleaseListPage extends ListRecords
{
    protected static string $resource = UserReleaseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
