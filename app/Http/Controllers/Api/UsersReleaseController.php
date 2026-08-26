<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use App\Http\Resources\ReleaseResource;
use App\Http\Resources\ReleaseCollection;

class UsersReleaseController extends Controller
{
    public function index(Request $request, User $user): ReleaseCollection
    {
        $search = $request->get('search', '');

        $releases = $this->getSearchQuery($search, $user)
            ->latest()
            ->paginate();

        return new ReleaseCollection($releases);
    }

    public function store(Request $request, User $user): ReleaseResource
    {
        $validated = $request->validate([
            'title' => ['required', 'string'],
            'art_cover' => ['required', 'string'],
            'published' => ['required', 'boolean'],
        ]);

        $release = $user->releases()->create($validated);

        return new ReleaseResource($release);
    }

    public function getSearchQuery(string $search, User $user)
    {
        return $user->releases()->where('title', 'like', "%{$search}%");
    }
}
