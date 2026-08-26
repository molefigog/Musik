<?php

namespace App\Filament\UserResources\Pages;

use App\Filament\UserResources\UserMusicResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class UserMusicCreatePage extends CreateRecord
{
    protected static string $resource = UserMusicResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Ensure the music belongs to a release owned by this user
        $release = \App\Models\Release::find($data['release_id']);
        if (!$release || $release->user_id !== Auth::id()) {
            throw ValidationException::withMessages([
                'release_id' => 'You can only add music to your own releases.',
            ]);
        }
        return $data;
    }
}
