<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\CpayService;
use Illuminate\Http\Request;

class WalletTopupController extends Controller
{
     protected CpayService $cpayService;

    public function __construct(CpayService $cpayService)
    {
        $this->cpayService = $cpayService;
    }

    public function topup(Request $request)
    {
        $validated = $request->validate([

            'msisdn' => 'required|string',
            'amount' => 'required|numeric|min:1',

            'description' => 'nullable|string',

            'destinationOperator' => 'nullable|string',

            'recipientKyc' => 'required|array',

            'recipientKyc.firstName' => 'required|string',
            'recipientKyc.lastName' => 'required|string',
            'recipientKyc.fullName' => 'required|string',

            'recipientKyc.idDocument' => 'required|array|min:1',
        ]);

        $response = $this->cpayService->walletTopupAdvance(
            msisdn: $validated['msisdn'],
            amount: $validated['amount'],
            recipientKyc: $validated['recipientKyc'],
            destinationOperator: $validated['destinationOperator'] ?? null,
            description: $validated['description'] ?? 'Wallet top-up'
        );

        return response()->json($response);
    }
}
