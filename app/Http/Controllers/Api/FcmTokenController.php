<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FcmToken;
use Illuminate\Http\Request;

class FcmTokenController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'token' => ['required', 'string', 'max:255'],
            'device_name' => ['nullable', 'string', 'max:255'],
        ]);

        FcmToken::updateOrCreate(
            ['token' => $validated['token']],
            [
                'user_id' => $request->user()->id,
                'device_name' => $validated['device_name'] ?? null,
            ],
        );

        return response()->json([
            'message' => 'FCM token saved successfully.',
        ]);
    }
}
