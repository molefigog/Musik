<?php

namespace App\Http\Controllers\Api;

use App\Models\Release;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use App\Http\Resources\MusicResource;
use App\Http\Resources\MusicCollection;
use Illuminate\Support\Facades\Auth;

class ReleasesMusicController extends Controller
{
    public function index(Request $request, Release $release): MusicCollection
    {
        abort_if($release->user_id !== Auth::id(), 403);

        $search = $request->get('search', '');

        $allMusic = $this->getSearchQuery($search, $release)
            ->latest()
            ->paginate();

        return new MusicCollection($allMusic);
    }

    public function store(Request $request, Release $release): MusicResource
    {
        abort_if($release->user_id !== Auth::id(), 403);

        $validated = $request->validate([
            'title' => ['required', 'string'],
            'price' => ['required'],
            'is_sold' => ['required', 'boolean'],
            'file_src' => ['nullable', 'file', 'max:15024'],
            'genre_id' => ['required'],
        ]);

        if ($request->hasFile('file_src')) {
            $validated['file_src'] = $request
                ->file('file_src')
                ->store('music', 'public');
            $validated['file_name'] = $request->file('file_src')->getClientOriginalName();
        }

        $music = $release->allMusic()->create($validated);

        return new MusicResource($music);
    }

    public function getSearchQuery(string $search, Release $release)
    {
        return $release->allMusic()->where('title', 'like', "%{$search}%");
    }
}
