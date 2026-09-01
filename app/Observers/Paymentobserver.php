<?php

namespace App\Observers;

use App\Models\Payment;
use App\Services\WalletService;


class PaymentObserver
{
    public function __construct(private WalletService $wallet) {}

    public function created(Payment $payment): void
    {
        $this->wallet->creditSeller($payment);
    }

    public function updated(Payment $payment): void
    {

        if ($payment->wasChanged('status') && $payment->status === 'completed') {
            $this->wallet->creditSeller($payment);
        }

        if ($payment->wasChanged('status') && $payment->status !== 'completed' && $payment->credited_at) {
            $this->wallet->reverseSaleCredit($payment, 'Status changed to ' . $payment->status);
        }
    }
}