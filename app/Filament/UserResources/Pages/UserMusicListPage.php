<?php

namespace App\Filament\UserResources\Pages;

use App\Filament\UserResources\UserMusicResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class UserMusicListPage extends ListRecords
{
    protected static string $resource = UserMusicResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
