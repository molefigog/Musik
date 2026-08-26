<?php

namespace App\Filament\UserResources\Pages;

use App\Filament\UserResources\UserProfileResource;
use Filament\Resources\Pages\EditRecord;

class UserProfileEditPage extends EditRecord
{
    protected static string $resource = UserProfileResource::class;
}
