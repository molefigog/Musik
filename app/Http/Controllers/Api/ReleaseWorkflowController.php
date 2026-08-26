<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Release;
use App\Models\Music;
use Illuminate\Support\Facades\Auth;

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

        $unprocessed = $release
            ->allMusic()
            ->where('is_processed', false)
            ->exists();

        if ($unprocessed) {
            return response()->json([
                'message' => 'Tracks still processing'
            ], 422);
        }

        $release->update([
            'status' => 'published',
            'published' => true,
            'is_completed' => true,
        ]);

        return response()->json([
            'published' => true
        ]);
    }
}
