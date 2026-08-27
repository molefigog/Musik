<?php

namespace App\Filament\Resources\Genres\Pages;

use App\Filament\Resources\Genres\GenreResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\DB;
use Filament\Notifications\Notification;

class ListGenres extends ListRecords
{
    protected static string $resource = GenreResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),

            Action::make('populateGenres')
                ->label('Populate Music Genres')
                ->icon('heroicon-o-musical-note')
                ->color('success')
                ->action(function () {
                    $genres = config('music.default_genres', []);
                    $insertedCount = 0;

                    foreach ($genres as $title) {
                        // Skip if the music genre title already exists
                        $exists = DB::table('genres')
                            ->where('title', $title)
                            ->exists();

                        if (!$exists) {
                            DB::table('genres')->insert([
                                'title'      => $title,
                                'art_cover'  => null, // Kept empty as requested
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]);
                            $insertedCount++;
                        }
                    }

                    if ($insertedCount > 0) {
                        Notification::make()
                            ->title("Added {$insertedCount} music genres!")
                            ->success()
                            ->send();
                    } else {
                        Notification::make()
                            ->title('All configured genres already exist.')
                            ->info()
                            ->send();
                    }
                }),
        ];
    }
}
