<?php

namespace App\Jobs;

use App\Models\Music;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class GenerateWaveform implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public Music $music) {}

    public function handle(): void
    {
        logger()->info('🚀 Waveform JOB START', [
            'music_id' => $this->music->id,
            'file_src' => $this->music->file_src,
        ]);

        $disk = Storage::disk('public');

        logger()->info('📁 Checking file existence');

        $exists = $disk->exists($this->music->file_src);

        logger()->info('📁 File exists check result', [
            'exists' => $exists,
            'path' => $this->music->file_src,
        ]);

        if (! $exists) {
            logger()->warning('❌ Waveform skipped: file not found');
            return;
        }

        $path = $disk->path($this->music->file_src);

        logger()->info('📂 Resolved file path', [
            'absolute_path' => $path,
        ]);

        if (!file_exists($path)) {
            logger()->error('❌ Physical file missing on disk', [
                'path' => $path,
            ]);
            return;
        }

        $audio = file_get_contents($path);

        logger()->info('🎧 Audio loaded into memory', [
            'size_bytes' => strlen($audio),
        ]);

        if ($audio === false) {
            logger()->error('❌ Failed to read audio file');
            return;
        }

        $width = 800;
        $height = 200;

        logger()->info('🖼 Creating waveform canvas', compact('width', 'height'));

        $image = imagecreatetruecolor($width, $height);

        if (! $image) {
            logger()->error('❌ Failed to create image canvas');
            return;
        }

        $bg = imagecolorallocate($image, 255, 255, 255);
        $wave = imagecolorallocate($image, 0, 0, 0);

        imagefill($image, 0, 0, $bg);

        $samples = strlen($audio);
        $step = max(1, intval($samples / $width));

        logger()->info('📊 Waveform processing stats', [
            'samples' => $samples,
            'step' => $step,
        ]);

        $mid = $height / 2;
        $x = 0;

        for ($i = 0; $i < $samples; $i += $step) {

            $chunk = substr($audio, $i, $step);

            $value = 0;

            foreach (str_split($chunk) as $char) {
                $value += ord($char);
            }

            $value = $value / max(1, strlen($chunk));

            $lineHeight = ($value / 255) * ($height / 2);

            imageline(
                $image,
                $x,
                $mid - $lineHeight,
                $x,
                $mid + $lineHeight,
                $wave
            );

            $x++;
        }

        $fileName = 'waveforms/' . $this->music->id . '.png';

        $fullPath = storage_path('app/public/' . $fileName);

        logger()->info('💾 Saving waveform image', [
            'file' => $fullPath,
        ]);

        if (!is_dir(dirname($fullPath))) {
            mkdir(dirname($fullPath), 0777, true);

            logger()->info('📁 Created waveform directory');
        }

        imagepng($image, $fullPath);

        imagedestroy($image);

        logger()->info('🧹 Image resource destroyed');

        $this->music->update([
            'waveform' => $fileName,
        ]);

        logger()->info('✅ DATABASE UPDATED', [
            'music_id' => $this->music->id,
            'waveform' => $fileName,
        ]);

        logger()->info('🎉 Waveform JOB COMPLETED');
    }
}
