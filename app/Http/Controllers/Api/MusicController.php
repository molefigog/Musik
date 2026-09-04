<?php

namespace App\Http\Controllers\Api;

use App\Models\Music;
use App\Models\Release;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use App\Http\Resources\MusicResource;
use App\Http\Resources\MusicCollection;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\MusicStoreRequest;
use App\Http\Requests\MusicUpdateRequest;
use Illuminate\Support\Facades\Auth;

class MusicController extends Controller
{

    public function index(Request $request)
    {
        $query = Music::with(['release', 'genre'])
            ->where('is_published', true);

        if ($request->routeIs('all-music.index')) {
            $query->whereHas('release', fn($release) => $release->where('user_id', Auth::id()));
        }
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
        if (request()->routeIs('all-music.show')) {
            abort_if($music->release?->user_id !== Auth::id(), 403);
        }

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

        $release = Release::query()
            ->whereKey($validated['release_id'] ?? null)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        if ($request->hasFile('file_src')) {
            $validated['file_src'] = $request
                ->file('file_src')
                ->store('public');
        }

        $music = $release->music()->create($validated);

        return new MusicResource($music);
    }

    public function temporaryUpload(Request $request)
    {
        $request->validate([
            'file_src' => ['required', 'file', 'mimes:mp3,wav', 'max:15024'],
        ]);

        $file = $request->file('file_src');
        $path = $file->store('temp/tracks/' . Auth::id(), 'public');

        return response()->json([
            'uploaded' => true,
            'temp_path' => $path,
            'file_name' => $file->getClientOriginalName(),
            'size' => $file->getSize(),
        ]);
    }

    public function updateTrack(Request $request): MusicResource
    {
        $validated = $request->validate([
            'release_id' => ['required', 'integer', 'exists:releases,id'],
            'title' => ['required', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'genre_id' => ['required', 'integer', 'exists:genres,id'],
            'temp_path' => ['required', 'string'],
            'file_name' => ['nullable', 'string'],
        ]);

        $release = Release::query()
            ->whereKey($validated['release_id'])
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $tempPath = ltrim($validated['temp_path'], '/');
        $expectedPrefix = 'temp/tracks/' . Auth::id() . '/';
        abort_unless(str_starts_with($tempPath, $expectedPrefix), 403);

        $disk = Storage::disk('public');
        abort_unless($disk->exists($tempPath), 404);

        $permanentPath = 'music/' . basename($tempPath);
        $disk->move($tempPath, $permanentPath);

        $music = $release->music()->create([
            'title' => $validated['title'],
            'price' => $validated['price'],
            'genre_id' => $validated['genre_id'],
            'file_src' => $permanentPath,
            'file_name' => $validated['file_name'] ?? basename($tempPath),
            'is_sold' => false,
        ]);

        return new MusicResource($music);
    }

    public function revertUpload(Request $request)
    {
        $validated = $request->validate([
            'temp_path' => ['required', 'string'],
        ]);

        $tempPath = ltrim($validated['temp_path'], '/');
        $expectedPrefix = 'temp/tracks/' . Auth::id() . '/';
        abort_unless(str_starts_with($tempPath, $expectedPrefix), 403);

        Storage::disk('public')->delete($tempPath);

        return response()->json(['reverted' => true]);
    }

    // public function show(Request $request, Music $music): MusicResource
    // {
    //     return new MusicResource($music);
    // }

    public function update(
        MusicUpdateRequest $request,
        Music $music
    ): MusicResource {
        abort_if($music->release?->user_id !== Auth::id(), 403);

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
        abort_if($music->release?->user_id !== Auth::id(), 403);

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
