<?php

namespace App\Observers;

use App\Models\Payment;
use App\Services\WalletService;
use Illuminate\Support\Facades\Log;

class PaymentObserver
{
    public function __construct(private WalletService $wallet) {}

    public function created(Payment $payment): void
    {
        $this->wallet->creditSeller($payment);
    }

    public function updated(Payment $payment): void
    {
        Log::info('Payment updated', [
            'payment_id' => $payment->id,
            'status' => $payment->status,
            'credited_at' => $payment->credited_at,
        ]);

        if ($payment->wasChanged('status') && $payment->status === 'completed') {
            $this->wallet->creditSeller($payment);
        }

        if ($payment->wasChanged('status') && $payment->status !== 'completed' && $payment->credited_at) {
            $this->wallet->reverseSaleCredit($payment, 'Status changed to ' . $payment->status);
        }
    }
}