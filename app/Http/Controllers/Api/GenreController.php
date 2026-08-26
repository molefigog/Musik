<?php

namespace App\Http\Controllers\Api;

use App\Models\Genre;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use App\Http\Resources\GenreResource;
use App\Http\Resources\GenreCollection;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\GenreStoreRequest;
use App\Http\Requests\GenreUpdateRequest;

class GenreController extends Controller
{
    public function index(Request $request): GenreCollection
    {
        $search = $request->get('search', '');

        $genres = $this->getSearchQuery($search)
            ->latest()
            ->paginate();

        return new GenreCollection($genres);
    }

    public function store(GenreStoreRequest $request): GenreResource
    {
        $validated = $request->validated();

        if ($request->hasFile('art_cover')) {
            $validated['art_cover'] = $request
                ->file('art_cover')
                ->store('public');
        }

        $genre = Genre::create($validated);

        return new GenreResource($genre);
    }

    public function show(Request $request, Genre $genre): GenreResource
    {
        return new GenreResource($genre);
    }

    public function update(
        GenreUpdateRequest $request,
        Genre $genre
    ): GenreResource {
        $validated = $request->validated();

        if ($request->hasFile('art_cover')) {
            if ($genre->art_cover) {
                Storage::delete($genre->art_cover);
            }

            $validated['art_cover'] = $request
                ->file('art_cover')
                ->store('public');
        }

        $genre->update($validated);

        return new GenreResource($genre);
    }

    public function destroy(Request $request, Genre $genre): Response
    {
        if ($genre->art_cover) {
            Storage::delete($genre->art_cover);
        }

        $genre->delete();

        return response()->noContent();
    }

    public function getSearchQuery(string $search)
    {
        return Genre::query()->where('title', 'like', "%{$search}%");
    }
}
