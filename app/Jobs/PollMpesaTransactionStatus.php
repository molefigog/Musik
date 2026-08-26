<?php

namespace App\Jobs;

use App\Http\Controllers\VclController;
use App\Models\Payment;
use App\Jobs\SendPaymentInvoice;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class PollMpesaTransactionStatus implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public string $conversationId,
        public int $attempt = 1
    ) {}

    public function handle(): void
    {
        $payments = Payment::query()
            ->where('type', 'mpesa')
            ->where('conversation_id', $this->conversationId)
            ->get();

        if ($payments->isEmpty()) {
            Log::warning('M-Pesa poll skipped: payment rows not found', [
                'conversation_id' => $this->conversationId,
                'attempt' => $this->attempt,
            ]);
            return;
        }

        if ($payments->every(fn(Payment $payment) => in_array(strtolower((string) $payment->status), ['completed', 'failed'], true))) {
            return;
        }

        $firstPayment = $payments->first();
        $existingRaw = json_decode((string) ($firstPayment?->raw_response ?? '{}'), true);
        $chargePayload = is_array($existingRaw) ? ($existingRaw['charge'] ?? null) : null;

        $queryReference = $this->resolveQueryReference($chargePayload);

        $vcl = new VclController($this->mpesaOptions());
        $queryResponse = $vcl->query([
            'input_QueryReference' => $queryReference,
            'input_Country' => 'LES',
            'input_ServiceProviderCode' => config('laravel-pesa.short_code'),
            'input_ThirdPartyConversationID' => $this->conversationId,
        ]);

        $normalizedStatus = $this->normalizeStatus($queryResponse, $firstPayment);
        $txnId = $queryResponse['output_OriginalTransactionID'] ?? null;

        $updatePayload = [
            'status' => $normalizedStatus,
            'raw_response' => json_encode([
                'charge' => $chargePayload,
                'query' => $queryResponse,
                'poll_attempt' => $this->attempt,
                'query_reference' => $queryReference,
            ]),
        ];

        if (!empty($txnId)) {
            $updatePayload['txn_id'] = $txnId;
        }

        Payment::query()
            ->where('type', 'mpesa')
            ->where('conversation_id', $this->conversationId)
            ->update($updatePayload);

        Log::info('M-Pesa poll result', [
            'conversation_id' => $this->conversationId,
            'attempt' => $this->attempt,
            'query_reference' => $queryReference,
            'status' => $normalizedStatus,
            'txn_id' => $txnId,
        ]);

        if ($normalizedStatus === 'completed') {
            $finalTxnId = (string) (Payment::query()
                ->where('type', 'mpesa')
                ->where('conversation_id', $this->conversationId)
                ->value('txn_id') ?? $this->conversationId);

            SendPaymentInvoice::dispatchFor($finalTxnId, 'mpesa');
        }

        if ($normalizedStatus === 'pending') {
            self::dispatch($this->conversationId, $this->attempt + 1)
                ->delay(now()->addSeconds($this->pollIntervalSeconds()));
        }
    }

    private function normalizeStatus(array $queryResponse, ?Payment $payment = null): string
    {
        $responseCode = strtoupper((string) ($queryResponse['output_ResponseCode'] ?? ''));
        $transactionStatusRaw = strtolower((string) ($queryResponse['output_ResponseTransactionStatus'] ?? ''));
        $transactionStatus = preg_replace('/[^a-z0-9]+/', '', $transactionStatusRaw) ?? '';
        $paymentAgeSeconds = $payment?->created_at ? now()->diffInSeconds($payment->created_at) : PHP_INT_MAX;
        $withinGraceWindow = $paymentAgeSeconds < $this->mpesaGraceSeconds();

        // Hard failures that should fail immediately.
        if (in_array($responseCode, ['INS-6', 'INS-2006', 'INS-2051'], true)) {
            return 'failed';
        }

        // These can happen temporarily before QTS index catches up.
        if (in_array($responseCode, ['INS-23', 'INS-6140'], true)) {
            return $withinGraceWindow ? 'pending' : 'failed';
        }

        if ($responseCode === 'INS-0' && in_array($transactionStatus, ['', 'completed', 'success', 'successful', 'transactioncomplete'], true)) {
            return 'completed';
        }

        if (in_array($transactionStatus, ['failed', 'cancelled', 'declined', 'rejected', 'reversed'], true)) {
            return 'failed';
        }

        if (in_array($transactionStatus, ['timeout', 'timedout', 'na'], true)) {
            return $withinGraceWindow ? 'pending' : 'failed';
        }

        return 'pending';
    }

    private function mpesaGraceSeconds(): int
    {
        return max(30, (int) env('MPESA_QTS_GRACE_SECONDS', 240));
    }

    private function resolveQueryReference(?array $chargePayload): string
    {
        $providerConversationId = (string) ($chargePayload['output_ConversationID'] ?? '');
        if ($providerConversationId !== '' && strtoupper($providerConversationId) !== 'N/A') {
            return $providerConversationId;
        }

        $providerTransactionId = (string) ($chargePayload['output_TransactionID'] ?? '');
        if ($providerTransactionId !== '' && strtoupper($providerTransactionId) !== 'N/A') {
            return $providerTransactionId;
        }

        return $this->conversationId;
    }

    private function pollIntervalSeconds(): int
    {
        return (int) env('MPESA_QUERY_POLL_INTERVAL', 15);
    }

    private function mpesaOptions(): array
    {
        return [
            'api_key' => config('laravel-pesa.api_key'),
            'public_key' => config('laravel-pesa.public_key'),
            'service_provider_code' => config('laravel-pesa.short_code'),
            'country' => 'LES',
            'currency' => 'LSL',
            'persistent_session' => true,
            'env' => config('laravel-pesa.env'),
        ];
    }
}
