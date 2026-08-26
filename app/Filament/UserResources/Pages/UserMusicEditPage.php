<?php

namespace App\Filament\UserResources\Pages;

use App\Filament\UserResources\UserMusicResource;
use Filament\Resources\Pages\EditRecord;

class UserMusicEditPage extends EditRecord
{
    protected static string $resource = UserMusicResource::class;
}
