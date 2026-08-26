<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Str;

class AuthController extends Controller
{

    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email' => 'required|string',
            'password' => 'required|string',
        ]);

        if (!auth()->attempt($credentials)) {
            throw ValidationException::withMessages([
                'email' => [trans('auth.failed')],
            ]);
        }

        $user = $request->user(); // or auth()->user()
        $user->tokens()->delete();
        $token = $user->createToken('auth-token');

        return response()->json([
            'token' => $token->plainTextToken,
            'user' => $user,
        ]);
    }

    public function register(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => 'required|string|unique:users,name',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|confirmed',
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        $token = $user->createToken('auth-token');
        return response()->json([
            'message' => 'Registration successful.',
            'token' => $token->plainTextToken,
            'user' => $user,
        ]);
    }

    public function updateProfile(Request $request): JsonResponse
    {
        $user = $request->user();

        Log::info('Update profile request started', [
            'user_id' => $user->id,
            'payload' => $request->all(),
        ]);

        $data = $request->validate([
            'name' => 'sometimes|string|unique:users,name,' . $user->id,
            'email' => 'sometimes|email|unique:users,email,' . $user->id,
            'tel' => 'sometimes|string|unique:users,tel,' . $user->id,
        ]);

        Log::info('Validated profile data', $data);

        if ($request->filled('password')) {

            Log::info('Password update requested');

            $request->validate([
                'current_password' => 'required|string',
                'password' => 'string|confirmed|min:6',
            ]);

            Log::info('Password validation passed');

            if (!Hash::check($request->current_password, $user->password)) {
                Log::warning('Invalid current password attempt', [
                    'user_id' => $user->id,
                ]);

                return response()->json([
                    'message' => 'The current password is incorrect.'
                ], 403);
            }

            $data['password'] = Hash::make($request->password);

            Log::info('Password updated successfully');
        }

        $user->update($data);

        Log::info('Profile updated successfully', [
            'user_id' => $user->id,
            'updated_fields' => array_keys($data),
        ]);

        return response()->json([
            'message' => 'Profile updated successfully.',
            'user' => $user->fresh(),
        ]);
    }
    public function me(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'message' => 'Unauthenticated'
            ], 401);
        }

        $user->load([
            'payments:id,user_id,music_id,service_id,service_type,title,amount,status,txn_id,type,description,created_at',
            'payments.music:id,title',
            'releases',
        ]);

        return response()->json([
            'user' => $user,
        ]);
    }
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json([
            'message' => 'Logged out successfully.'
        ]);
    }
    public function googleCallback(Request $request)
    {
        $googleUser = Socialite::driver('google')->stateless()->user();

        $user = User::updateOrCreate(
            ['email' => $googleUser->getEmail()],
            [
                'name' => $googleUser->getName(),
                'google_id' => $googleUser->getId(),
                'password' => Hash::make(Str::random(32)),
            ]
        );

        // remove old tokens
        $user->tokens()->delete();

        $token = $user->createToken('auth-token');

        return response()->json([
            'token' => $token->plainTextToken,
            'user' => $user,
        ]);
    }
    public function googleRedirect()
    {
        return Socialite::driver('google')->stateless()->redirect();
    }
}
