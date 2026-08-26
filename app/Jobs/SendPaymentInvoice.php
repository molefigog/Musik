<?php

namespace App\Jobs;

use App\Mail\SimpleInvoiceMail;
use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendPaymentInvoice implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public string $txnId,
        public string $type
    ) {}

    public static function dispatchFor(string $txnId, string $type): void
    {
        $txnId = trim($txnId);
        $type = trim($type);

        if ($txnId === '' || $type === '') {
            return;
        }

        $cacheKey = sprintf('invoice_dispatched:%s:%s', strtolower($type), $txnId);

        if (Cache::add($cacheKey, true, now()->addHours(24))) {
            self::dispatch($txnId, $type);
        }
    }

    public function handle(): void
    {
        $payments = Payment::query()
            ->with([
                'user:id,name,email',
                'music:id,title',
            ])
            ->where('txn_id', $this->txnId)
            ->where('type', $this->type)
            ->where('status', 'completed')
            ->get();

        if ($payments->isEmpty()) {
            Log::warning('Invoice job skipped: no completed payments found', [
                'txn_id' => $this->txnId,
                'type' => $this->type,
            ]);
            return;
        }

        $user = $payments->first()->user;
        $email = $user?->email;

        if (empty($email)) {
            Log::warning('Invoice job skipped: user email not found', [
                'txn_id' => $this->txnId,
                'type' => $this->type,
                'user_id' => $payments->first()->user_id,
            ]);
            return;
        }

        $lineItems = $payments->map(function (Payment $payment): array {
            $name = $payment->music?->title
                ?? $payment->description
                ?? 'Purchased item';

            return [
                'name' => (string) $name,
                'amount' => (float) $payment->amount,
                'music_id' => $payment->music_id,
            ];
        })->values()->all();

        $payload = [
            'customer_name' => (string) ($user?->name ?? 'Customer'),
            'txn_id' => $this->txnId,
            'type' => $this->type,
            'total' => (float) $payments->sum('amount'),
            'currency' => 'LSL',
            'issued_at' => now()->toDateTimeString(),
            'items' => $lineItems,
        ];

        Mail::to($email)->send(new SimpleInvoiceMail($payload));

        Log::info('Invoice mail queued and sent', [
            'txn_id' => $this->txnId,
            'type' => $this->type,
            'email' => $email,
            'items' => count($lineItems),
        ]);
    }
}
