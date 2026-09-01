<?php

namespace App\Http\Controllers\Api;

use App\Models\Music;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use App\Http\Resources\MusicResource;
use App\Http\Resources\MusicCollection;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\MusicStoreRequest;
use App\Http\Requests\MusicUpdateRequest;

class MusicController extends Controller
{

    public function index(Request $request)
    {
        $query = Music::with(['release', 'genre'])
            ->where('is_published', true);
        // SEARCH (title, release, genre)
        if ($request->search) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }
        // GENRE
        if ($request->genre_id) {
            $query->where('genre_id', $request->genre_id);
        }
        // RELEASE
        if ($request->release_id) {
            $query->where('release_id', $request->release_id);
        }
        // PRICE
        if ($request->price === 'free') {
            $query->where('price', 0);
        }
        if ($request->price === 'paid') {
            $query->where('price', '>', 0);
        }
        // LETTER FILTER
        if ($request->letter) {
            $query->where('title', 'like', $request->letter . '%');
        }

        $music = $query->latest()->get()
            ->map(fn($music) => $this->formatMusic($music));

        return response()->json([
            'data' => $music
        ]);
    }

    public function show(Music $music)
    {
        $music->load(['release', 'genre']);

        return response()->json([
            'data' => $this->formatMusic($music),
        ]);
    }

    private function formatMusic($music)
    {
        return [
            'id' => $music->id,
            'title' => $music->title,
            'file_name' => $music->file_name,
            'file_src' => asset('storage/' . $music->file_src),
            'waveform' => $music->waveform ? asset('storage/' . $music->waveform) : null,
            'duration' => $music->duration,
            'size' => $music->size,
            'price' => $music->price,
            'extension' => $music->extension,

            'is_sold' => (bool) $music->is_sold,

            'release_id' => $music->release_id,
            'genre_id' => $music->genre_id,

            'release' => $music->release ? [
                'id' => $music->release->id,
                'title' => $music->release->title,
                'cover_art' => $music->release->art_cover ? asset('storage/' . $music->release->art_cover) : null,
            ] : null,

            'genre' => $music->genre ? [
                'id' => $music->genre->id,
                'title' => $music->genre->title,

            ] : null,
        ];
    }
    public function store(MusicStoreRequest $request): MusicResource
    {
        $validated = $request->validated();

        if ($request->hasFile('file_src')) {
            $validated['file_src'] = $request
                ->file('file_src')
                ->store('public');
        }

        $music = Music::create($validated);

        return new MusicResource($music);
    }

    // public function show(Request $request, Music $music): MusicResource
    // {
    //     return new MusicResource($music);
    // }

    public function update(
        MusicUpdateRequest $request,
        Music $music
    ): MusicResource {
        $validated = $request->validated();

        if ($request->hasFile('file_src')) {
            if ($music->file_src) {
                Storage::delete($music->file_src);
            }

            $validated['file_src'] = $request
                ->file('file_src')
                ->store('public');
        }

        $music->update($validated);

        return new MusicResource($music);
    }

    public function destroy(Request $request, Music $music): Response
    {
        if ($music->file_src) {
            Storage::delete($music->file_src);
        }

        $music->delete();

        return response()->noContent();
    }

    public function getSearchQuery(string $search)
    {
        return Music::query()->where('title', 'like', "%{$search}%");
    }
}