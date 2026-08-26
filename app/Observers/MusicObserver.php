<?php

namespace App\Observers;

use App\Models\Music;
use Owenoj\LaravelGetId3\GetId3;
use Illuminate\Support\Facades\Storage;
use App\Jobs\GenerateWaveform;

class MusicObserver
{
    /**
     * Handle the Music "created" event.
     */
    public function created(Music $music): void
    {
        if (blank($music->file_src)) {
            return;
        }

        $disk = Storage::disk('public');

        if (! $disk->exists($music->file_src)) {
            return;
        }

        $file = $disk->path($music->file_src);

        if (! file_exists($file)) {
            return;
        }

        $pathInfo = pathinfo($music->file_src);

        $extension = strtolower($pathInfo['extension'] ?? '');

        $storedFilename = $pathInfo['basename'] ?? null;

        $originalName = $music->file_name;

        if (blank($originalName)) {
            $originalName = $pathInfo['filename'] ?? null;
        }
        $duration = null;

        try {
            $track = new GetId3($file);
            $track->extractInfo();
            $duration = $track->getPlaytime();
        } catch (\Throwable $e) {
            logger()->error('Audio metadata extraction failed', [
                'music_id' => $music->id,
                'error' => $e->getMessage(),
            ]);
        }
        $sizeMB = round(filesize($file) / (1024 * 1024), 2);

        $music->forceFill([
            'duration' => $duration,
            'size' => $sizeMB,
            'extension' => $extension,
            'file_name' => $originalName,
            'file_src' => $music->file_src,
            'waveform' => null,
            'is_published' => false,
        ])->saveQuietly();
    }

    /**
     * Handle the Music "updated" event.
     */
    public function updated(Music $music): void
    {
        //
    }


    /**
     * Handle the Music "restored" event.
     */
    public function restored(Music $music): void
    {
        //
    }

    /**
     * Handle the Music "force deleted" event.
     */
    public function deleted(Music $music): void
    {
        $this->deleteFile($music);
    }

    public function forceDeleted(Music $music): void
    {
        $this->deleteFile($music);
    }

    private function deleteFile(Music $music): void
    {
        if (!$music->file_src) {
            return;
        }

        $disk = Storage::disk('public');

        if ($disk->exists($music->file_src)) {
            $disk->delete($music->file_src);
        }
    }
}
