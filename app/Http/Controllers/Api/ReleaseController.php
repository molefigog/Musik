<?php

namespace App\Http\Controllers\Api;

use App\Models\Release;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use App\Http\Resources\ReleaseResource;
use Illuminate\Support\Facades\Storage;
use App\Http\Resources\ReleaseCollection;
use App\Http\Requests\ReleaseStoreRequest;
use App\Http\Requests\ReleaseUpdateRequest;
use Illuminate\Support\Facades\Auth;

class ReleaseController extends Controller
{
    public function index(Request $request): ReleaseCollection
    {
        $search = $request->get('search', '');

        $releases = $this->getSearchQuery($search)
            ->where('user_id', Auth::id())
            ->latest()
            ->paginate();

        return new ReleaseCollection($releases);
    }

    public function store(ReleaseStoreRequest $request): ReleaseResource
    {
        $validated = $request->validated();

        if ($request->hasFile('art_cover')) {
            $validated['art_cover'] = $request
                ->file('art_cover')
                ->store('covers', 'public');
        }

        $validated['user_id'] = Auth::id();
        $release = Release::create($validated);

        return new ReleaseResource($release);
    }

    public function show(Request $request, Release $release): ReleaseResource
    {
        abort_if($release->user_id !== Auth::id(), 403);

        return new ReleaseResource($release);
    }

    public function update(
        ReleaseUpdateRequest $request,
        Release $release
    ): ReleaseResource {
        abort_if($release->user_id !== Auth::id(), 403);

        $validated = $request->validated();

        if ($request->hasFile('art_cover')) {
            if ($release->art_cover) {
                Storage::disk('public')->delete($release->art_cover);
            }

            $validated['art_cover'] = $request
                ->file('art_cover')
                ->store('covers', 'public');
        }

        $release->update($validated);

        return new ReleaseResource($release);
    }

    public function destroy(Request $request, Release $release): Response
    {
        abort_if($release->user_id !== Auth::id(), 403);

        if ($release->art_cover) {
            Storage::disk('public')->delete($release->art_cover);
        }

        $release->delete();

        return response()->noContent();
    }

    public function getSearchQuery(string $search)
    {
        return Release::query()->where('title', 'like', "%{$search}%");
    }
}
