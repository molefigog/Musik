<?php

namespace App\Http\Controllers\Api;

use App\Models\Genre;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use App\Http\Resources\MusicResource;
use App\Http\Resources\MusicCollection;

class GenresMusicController extends Controller
{
    public function index(Request $request, Genre $genre): MusicCollection
    {
        $search = $request->get('search', '');

        $allMusic = $this->getSearchQuery($search, $genre)
            ->latest()
            ->paginate();

        return new MusicCollection($allMusic);
    }

    public function store(Request $request, Genre $genre): MusicResource
    {
        $validated = $request->validate([
            'title' => ['required', 'string'],
            'price' => ['required'],
            'is_sold' => ['required', 'boolean'],
            'file_src' => ['nullable', 'file', 'max:15024'],
        ]);

        if ($request->hasFile('file_src')) {
            $validated['file_src'] = $request
                ->file('file_src')
                ->store('public');
        }

        $music = $genre->music()->create($validated);

        return new MusicResource($music);
    }

    public function getSearchQuery(string $search, Genre $genre)
    {
        return $genre->music()->where('title', 'like', "%{$search}%");
    }
}
