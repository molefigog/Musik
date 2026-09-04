<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Release;
use App\Models\Music;
use App\Jobs\GenerateWaveform;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ReleaseWorkflowController extends Controller
{
    public function start(Request $request)
    {
        $release = Release::create([
            'title' => '',
            'user_id' => Auth::id(),
            'status' => 'draft',
        ]);

        return response()->json($release);
    }
    public function autosave(Request $request, Release $release)
    {
        abort_if(
            $release->user_id !== Auth::id(),
            403
        );

        $release->update([
            'title' => $request->title,
            'published' => $request->published,
            'last_autosaved_at' => now(),
        ]);

        return response()->json([
            'saved' => true
        ]);
    }
    public function uploadTracks(Request $request, Release $release)
    {
        abort_if(
            $release->user_id !== Auth::id(),
            403
        );

        $request->validate([
            'tracks.*' => 'required|file|mimes:mp3,wav'
        ]);

        foreach ($request->file('tracks') as $file) {

            $path = $file->store('music', 'public');

            Music::create([
                'title' => pathinfo(
                    $file->getClientOriginalName(),
                    PATHINFO_FILENAME
                ),
                'original_filename' =>
                $file->getClientOriginalName(),
                'file_src' => $path,
                'size' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
                'release_id' => $release->id,
                'is_processed' => false,
            ]);
        }

        $release->update([
            'status' => 'processing'
        ]);

        return response()->json([
            'uploaded' => true
        ]);
    }
    public function resume(Release $release)
    {
        abort_if(
            $release->user_id !== Auth::id(),
            403
        );

        return response()->json([
            'release' => $release,
            'tracks' => $release->allMusic
        ]);
    }
    public function publish(Release $release)
    {
        abort_if(
            $release->user_id !== Auth::id(),
            403
        );

        if (!$release->art_cover) {
            return response()->json([
                'message' => 'Artwork required'
            ], 422);
        }

        if ($release->allMusic()->count() === 0) {
            return response()->json([
                'message' => 'At least one track required'
            ], 422);
        }

        $release->update([
            'published' => true,
        ]);

        return response()->json([
            'published' => true
        ]);
    }

    public function saveWaveform(Request $request, Music $music)
    {
        abort_if($music->release?->user_id !== Auth::id(), 403);

        $request->validate([
            'waveform_file' => ['required', 'file', 'mimes:png,jpg,jpeg', 'max:2048'],
        ]);

        if ($music->waveform && Storage::disk('public')->exists($music->waveform)) {
            Storage::disk('public')->delete($music->waveform);
        }

        $path = $request->file('waveform_file')->store('waveforms', 'public');
        $music->update([
            'waveform' => $path,
            'is_published' => $request->boolean('is_published', true),
        ]);

        return response()->json([
            'saved' => true,
            'music' => $music->fresh(),
            'release_id' => $music->release_id,
        ]);
    }

    public function audio(Music $music)
    {
        abort_if($music->release?->user_id !== Auth::id(), 403);
        abort_if(blank($music->file_src), 404);

        $disk = Storage::disk('public');
        abort_unless($disk->exists($music->file_src), 404);

        return response()->file($disk->path($music->file_src), [
            'Content-Type' => $disk->mimeType($music->file_src) ?: 'audio/mpeg',
            'Content-Disposition' => 'inline; filename="' . addslashes($music->file_name ?: basename($music->file_src)) . '"',
            'Accept-Ranges' => 'bytes',
            'Cache-Control' => 'private, no-store',
        ]);
    }

    public function generateWaveform(Music $music)
    {
        abort_if($music->release?->user_id !== Auth::id(), 403);

        if (! $music->waveform) {
            GenerateWaveform::dispatchSync($music);
        }

        return response()->json(['queued' => true]);
    }
}
