<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class SessionController extends Controller
{
    public function index(Request $request)
    {
        $sessions = DB::table('sessions')
            ->where('user_id', $request->user()->id)
            ->orderByDesc('last_activity')
            ->get()
            ->map(function ($session) use ($request) {

                return [
                    'id' => $session->id,

                    'ip_address' => $session->ip_address,

                    'user_agent' => $session->user_agent,

                    'is_current' =>
                        $session->id === $request->session()->getId(),

                    'last_active' => now()
                        ->setTimestamp($session->last_activity)
                        ->diffForHumans(),
                ];
            });

        return response()->json($sessions);
    }

    public function destroy(Request $request, string $session)
    {
        DB::table('sessions')
            ->where('user_id', $request->user()->id)
            ->where('id', $session)
            ->delete();

        return response()->json([
            'message' => 'Session removed',
        ]);
    }

    public function destroyOther(Request $request)
    {
        DB::table('sessions')
            ->where('user_id', $request->user()->id)
            ->where('id', '!=', $request->session()->getId())
            ->delete();

        return response()->json([
            'message' => 'Other sessions removed',
        ]);
    }
}
