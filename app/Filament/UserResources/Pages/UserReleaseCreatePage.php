<?php

namespace App\Filament\UserResources\Pages;

use App\Filament\UserResources\UserReleaseResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class UserReleaseCreatePage extends CreateRecord
{
    protected static string $resource = UserReleaseResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = Auth::id();
        return $data;
    }
}
