<?php

namespace App\Http\Controllers;

use App\Services\WalletService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

/**
 * Separate from WalletController (API) because this is hit directly
 * by the browser (Filament action opens it in a new tab) and relies
 * on the panel's web session, not a Sanctum token.
 */
class WalletPdfController extends Controller
{
    public function __construct(private WalletService $wallet) {}

    /**
     * Streams the PDF inline - browsers render their native PDF
     * viewer, which already has its own download/print buttons.
     */
    public function preview(Request $request)
    {
        $data = $request->validate([
            'from' => 'required|date',
            'to'   => 'required|date|after_or_equal:from',
            'type' => 'in:purchases,sales',
        ]);

        $pdf = $this->wallet->summaryPdf(
            Auth::user(),
            Carbon::parse($data['from']),
            Carbon::parse($data['to']),
            $data['type'] ?? 'purchases'
        );

        return $pdf->stream('wallet-summary.pdf'); // inline, not download
    }
}

/*
|--------------------------------------------------------------------
| routes/web.php - protect with whatever guard your Filament panel
| uses (default 'web'), so the browser tab shares the admin session.
|--------------------------------------------------------------------

Route::middleware(['auth'])->group(function () {
    Route::get('/wallet/summary/preview', [WalletPdfController::class, 'preview'])
        ->name('wallet.summary.preview');
});
*/