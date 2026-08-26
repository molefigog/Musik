<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PaymentGateway;
use Illuminate\Http\JsonResponse;

class PaymentGatewayController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'data' => PaymentGateway::query()
                ->where('enabled', true)
                ->orderBy('id')
                ->get(['name', 'slug']),
        ]);
    }
}
