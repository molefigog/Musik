<?php

namespace App\Http\Controllers;

use App\Services\WalletService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

/**
 * Thin HTTP layer - all logic lives in WalletService so the exact
 * same code path is used by Filament actions.
 */
class WalletController extends Controller
{
    public function __construct(private WalletService $wallet) {}

    public function topUp(Request $request)
    {
        $data = $request->validate(['amount' => 'required|numeric|min:1']);

        try {
            $txn = $this->wallet->topUp($request->user(), (float) $data['amount']);
            return response()->json(['message' => 'Wallet topped up', 'transaction' => $txn]);
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function cashOut(Request $request)
    {
        $data = $request->validate(['amount' => 'required|numeric|min:1']);

        try {
            $txn = $this->wallet->cashOut($request->user(), (float) $data['amount']);
            return response()->json(['message' => 'Wallet cashed out', 'transaction' => $txn]);
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function summary(Request $request)
    {
        $data = $this->validateRange($request);

        return response()->json(
            $this->wallet->summaryTotals(
                $request->user(),
                Carbon::parse($data['from']),
                Carbon::parse($data['to'])
            )
        );
    }

    public function purchases(Request $request)
    {
        $data = $this->validateRange($request);

        return response()->json(
            $this->wallet->purchases($request->user(), Carbon::parse($data['from']), Carbon::parse($data['to']))
        );
    }

    public function sales(Request $request)
    {
        $data = $this->validateRange($request);

        return response()->json(
            $this->wallet->sales($request->user(), Carbon::parse($data['from']), Carbon::parse($data['to']))
        );
    }

    public function summaryPdf(Request $request)
    {
        $data = $this->validateRange($request);

        $pdf = $this->wallet->summaryPdf(
            $request->user(),
            Carbon::parse($data['from']),
            Carbon::parse($data['to']),
            $data['type'] ?? 'purchases'
        );

        // ?download=1 forces a download instead of inline preview
        return $request->boolean('download')
            ? $pdf->download('wallet-summary.pdf')
            : $pdf->stream('wallet-summary.pdf');
    }

    private function validateRange(Request $request): array
    {
        return $request->validate([
            'from' => 'required|date',
            'to'   => 'required|date|after_or_equal:from',
            'type' => 'in:purchases,sales',
        ]);
    }
}

/*
|--------------------------------------------------------------------
| routes/api.php
|--------------------------------------------------------------------

Route::middleware('auth:sanctum')->prefix('wallet')->group(function () {
    Route::post('/top-up', [WalletController::class, 'topUp']);
    Route::post('/cash-out', [WalletController::class, 'cashOut']);
    Route::get('/summary', [WalletController::class, 'summary']);
    Route::get('/purchases', [WalletController::class, 'purchases']);
    Route::get('/sales', [WalletController::class, 'sales']);
    Route::get('/summary/pdf', [WalletController::class, 'summaryPdf']);
});
*/
