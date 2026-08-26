<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use App\Http\Resources\PaymentResource;
use App\Http\Resources\PaymentCollection;

class UsersPaymentController extends Controller
{
    public function index(Request $request, User $user): PaymentCollection
    {
        $search = $request->get('search', '');

        $payments = $this->getSearchQuery($search, $user)
            ->with('music:id,title')
            ->latest()
            ->paginate();

        return new PaymentCollection($payments);
    }

    public function store(Request $request, User $user): PaymentResource
    {
        $validated = $request->validate([
            'amount' => ['required'],
            'status' => ['required', 'string'],
            'txn_id' => ['required', 'string'],
            'msisdn' => ['required', 'string'],
            'conversation_id' => ['required', 'string'],
            'type' => ['required', 'string'],
            'raw_response' => ['nullable'],
            'statusCode' => ['nullable', 'string'],
            'description' => ['nullable', 'string'],
            'music_id' => ['required'],
        ]);

        $validated['raw_response'] = json_encode(
            $validated['raw_response'],
            true
        );

        $payment = $user->payments()->create($validated);

        return new PaymentResource($payment);
    }

    public function getSearchQuery(string $search, User $user)
    {
        return $user->payments()->where('status', 'like', "%{$search}%");
    }
}
