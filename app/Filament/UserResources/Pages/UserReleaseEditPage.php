<?php

namespace App\Filament\UserResources\Pages;

use App\Filament\UserResources\UserReleaseResource;
use Filament\Resources\Pages\EditRecord;

class UserReleaseEditPage extends EditRecord
{
    protected static string $resource = UserReleaseResource::class;
}
