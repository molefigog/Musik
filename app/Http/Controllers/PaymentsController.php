<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\CpayService;
use App\Services\VclService;
use App\Models\Music;
use App\Models\Payment;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Response;
use App\Services\PayPalService;
use App\Jobs\PollMpesaTransactionStatus;
use App\Jobs\SendPaymentInvoice;
use App\Services\TaskProvisioningService;

class PaymentsController extends Controller
{
    public function makePayment(Request $request, CpayService $cpay)
    {
        $request->validate([
            'msisdn' => 'required|string',
            'amount' => 'required|numeric|min:0.01',
            'item_type' => 'nullable|string',
            'item_id' => 'nullable',
            'service_type' => 'nullable|string|in:beat,recording,artwork',
        ]);

        $transactionId = 'CPAY_' . time();
        $result = $cpay->makePayment(
            $request->msisdn,
            $request->amount,
            'jackets'
        );
        $data = $result['return'] ?? [];
        $itemType = strtolower((string) $request->input('item_type', 'music'));
        $itemIds = $this->extractPaymentItemIds($request->input('item_id'), $itemType);
        $cpayTransactionId = $data['ExtTransactionId'] ?? null;

        $rows = $this->createPaymentRows(
            $itemIds,
            $itemType,
            [
                'user_id' => Auth::id(),
                'amount' => $request->amount,
                'msisdn' => $request->msisdn,
                'txn_id' => $transactionId,
                'conversation_id' => $cpayTransactionId ?? $transactionId,
                'type' => 'cpay',
                'status' => 'pending',
                'description' => $request->input('description', 'Music purchase'),
                'service_type' => $request->input('service_type'),
                'title' => $request->input('title'),
                'raw_response' => json_encode($result),
            ]
        );

        return response()->json([
            'success' => in_array((string) ($data['StatusCode'] ?? ''), ['0000', '200'], true),
            'status' => $data['ReasonCode'] ?? null,
            'message' => $data['Description'] ?? null,
            'transaction_id' => $cpayTransactionId ?? $transactionId,
            'payment_transaction_id' => $transactionId,
            'cpay_transaction_id' => $cpayTransactionId,
            'payment' => $rows->first(),
            'payments' => $rows,
        ]);
    }
    public function confirmPayment(Request $request, CpayService $cpay)
    {
        $request->validate([
            'transaction_id' => 'required|string',
            'msisdn' => 'required|string',
            'amount' => 'required|numeric|min:0.01',
            'otp' => 'required|string',
        ]);

        $payment = Payment::where('txn_id', $request->transaction_id)
            ->where('type', 'cpay')
            ->orWhere(function ($query) use ($request) {
                $query->where('conversation_id', $request->transaction_id)
                    ->where('type', 'cpay');
            })
            ->first();
        $paymentTransactionId = $payment?->txn_id ?? $request->transaction_id;

        $result = $cpay->confirmPayment(
            $request->transaction_id,
            $request->msisdn,
            $request->amount,
            $request->otp
        );

        $raw = $result['raw'] ?? [];
        $success = $result['success'] === true;

        $status = $success ? 'completed' : 'failed';
        Payment::where('txn_id', $paymentTransactionId)
            ->where('type', 'cpay')
            ->update([
                'status' => $status,
                'raw_response' => json_encode($result),
            ]);

        $payment = Payment::where('txn_id', $paymentTransactionId)
            ->where('type', 'cpay')
            ->first();

        if ($payment && !empty($raw['cPayTransactionId'])) {
            Payment::where('txn_id', $paymentTransactionId)
                ->where('type', 'cpay')
                ->update([
                    'conversation_id' => $raw['cPayTransactionId'],
                ]);
        }

        if ($success) {
            $this->provisionCompletedPayments($paymentTransactionId);
            SendPaymentInvoice::dispatchFor((string) $paymentTransactionId, 'cpay');
        }

        return response()->json([
            'success' => $success,
            'status' => $status,
            'message' => $raw['Description'] ?? 'Unknown response',
            'transaction_id' => $raw['extTransactionId'] ?? null,
            'cpay_transaction_id' => $raw['cPayTransactionId'] ?? null,
            'payment_transaction_id' => $paymentTransactionId,
            'raw' => $result
        ]);
    }
    public function processCard(Request $request, CpayService $cpay)
    {
        Log::info('processCard HIT', [
            'method' => $request->method(),
            'headers' => $request->headers->all(),
            'user' => optional($request->user())->id,
            'ip' => $request->ip(),
        ]);
        $request->validate([
            'amount' => 'required|numeric|min:1',
            'msisdn' => 'required|string',
            'item_type' => 'nullable|string',
            'item_id' => 'nullable',
            'service_type' => 'nullable|string|in:beat,recording,artwork',
        ]);

        $transactionId = 'GW_' . time();
        $payload = [
            'extTransactionId' => $transactionId,
            'amount' => number_format($request->amount, 2, '.', ''),
            'msisdn' => $request->msisdn,
            'currency' => 'LSL',
            //'redirectUrl' =>'https://4675-129-232-76-97.ngrok-free.app/api/payment/callback',
            'redirectUrl' => config('app.url') . '/api/payment/callback',
            //    'redirectUrl' => 'https://organic-detector-spots-audio.trycloudflare.com/api/payment/callback',
            'email' => $request->email,
        ];

        $response = $cpay->cardPayment($payload);

        $itemType = strtolower((string) $request->input('item_type', 'music'));
        $itemIds = $this->extractPaymentItemIds($request->input('item_id'), $itemType);

        $this->createPaymentRows(
            $itemIds,
            $itemType,
            [
                'user_id' => Auth::id(),
                'txn_id' => $transactionId,
                'amount' => $payload['amount'],
                'msisdn' => $payload['msisdn'],
                'conversation_id' => $transactionId,
                'type' => 'card',
                'status' => 'pending',
                'description' => $request->input('description', 'Music purchase'),
                'service_type' => $request->input('service_type'),
                'title' => $request->input('title'),
                'raw_response' => json_encode($response),
            ]
        );

        return response()->json([
            'type' => $response['type'] ?? 'json',
            'html' => $response['html'] ?? null,
            'redirect_url' => $response['redirect_url'] ?? null,
            'transaction_id' => $transactionId
        ]);
    }
    public function callback(Request $request)
    {
        $status = $this->updatePayment($request->all());
        $txnId = $request->input('ExtTransactionId');
        $frontend = rtrim((string) config('app.frontend_url', 'http://localhost:9000'), '/');

        return response()->view('payment.result', [
            'status' => $status,
            'txnId' => $txnId,
            'redirect' => $frontend . '/'
        ]);
    }
    private function updatePayment(array $data)
    {
        $txnId = $data['ExtTransactionId'] ?? null;
        if (!$txnId) {
            Log::error('Missing ExtTransactionId', $data);
            return 'failed';
        }
        $payments = Payment::where('txn_id', $txnId)->get();
        if ($payments->isEmpty()) {
            Log::error('Payment NOT FOUND', $data);
            return 'failed';
        }

        $status = ($data['ReasonCode'] ?? null) === 'TransactionComplete'
            ? 'completed'
            : 'failed';

        Payment::where('txn_id', $txnId)->update([
            'status' => $status,
            'cpay_transaction_id' => $data['CPayTransactionId'] ?? null,
            'raw_response' => json_encode($data),
        ]);

        if ($status === 'completed') {
            $this->provisionCompletedPayments($txnId);
            $type = (string) (Payment::where('txn_id', $txnId)->value('type') ?? 'card');
            SendPaymentInvoice::dispatchFor((string) $txnId, $type);
        }

        Log::info('CPay updated', [
            'txn_id' => $txnId,
            'status' => $status
        ]);

        return $status;
    }

    private function provisionCompletedPayments(string $txnId): void
    {
        $provisioning = app(TaskProvisioningService::class);

        Payment::query()
            ->where('txn_id', $txnId)
            ->where('status', 'completed')
            ->get()
            ->each(fn(Payment $payment) => $provisioning->createFromPayment($payment));
    }

    private array $options;
    public function __construct()
    {
        $this->options = [
            'api_key' => config('laravel-pesa.api_key'),
            'public_key' => config('laravel-pesa.public_key'),
            'service_provider_code' => config('laravel-pesa.short_code'),
            'country' => 'LES',
            'currency' => 'LSL',
            'persistent_session' => true,
            'env' => config('laravel-pesa.env')
        ];
    }

    public function charge(Request $request)
    {
        Log::info("Charge route accessed.");

        $number = $request->input('input_CustomerMSISDN');
        $mssid = '266' . $number;
        $conversationId = 'NID' . time();

        $vclController = new VclController($this->options);
        $response = $vclController->c2b([
            'input_Amount' => $request->input('input_Amount'),
            'input_Country' => 'LES',
            'input_Currency' => 'LSL',
            'input_CustomerMSISDN' => $mssid,
            'input_ServiceProviderCode' => config('laravel-pesa.short_code'),
            'input_ThirdPartyConversationID' => $conversationId,
            'input_TransactionReference' => 'nidptyltd',
            'input_PurchasedItemsDesc' => $request->input('input_PurchasedItemsDesc')
        ]);

        Log::info("Charge response:", $response);
        $status = ($response['output_ResponseCode'] ?? null) === 'INS-0' ? 'pending' : 'failed';
        // payments.txn_id is non-nullable; use conversation id until provider returns original transaction id.
        $txnId = $conversationId;
        $itemType = strtolower((string) $request->input('item_type', 'music'));
        $itemId = $request->input('item_id', $request->input('input_MusicId'));
        $itemType = strtolower((string) $itemType);
        $itemIds = $this->extractPaymentItemIds($itemId, $itemType);

        $payments = $this->createPaymentRows(
            $itemIds,
            $itemType,
            [
                'amount' => $request->input('input_Amount'),
                'status' => $status,
                'txn_id' => $txnId,
                'msisdn' => $mssid,
                'conversation_id' => $conversationId,
                'type' => 'mpesa',
                'raw_response' => json_encode([
                    'charge' => $response,
                    'query' => null
                ]),
                'description' => $request->input('input_PurchasedItemsDesc'),
                'user_id' => Auth::id(),
            ]
        );

        if ($status === 'pending') {
            PollMpesaTransactionStatus::dispatch($conversationId, 1);
        }

        return response()->json([
            'payment' => $payments->first(),
            'payments' => $payments,
            'charge' => $response,
            'query' => null
        ]);
    }

    public function chargeServices(Request $request)
    {
        $request->merge([
            'item_type' => 'service',
            'item_id' => $request->input('item_id', $request->input('input_MusicId')),
        ]);

        return $this->charge($request);
    }

    public function mpesaStatus(Request $request)
    {
        $request->validate([
            'conversation_id' => 'required|string',
        ]);

        $conversationId = $request->input('conversation_id');

        $payments = Payment::query()
            ->where('type', 'mpesa')
            ->where('conversation_id', $conversationId)
            ->get();

        if ($payments->isEmpty()) {
            return response()->json([
                'message' => 'Payment not found',
            ], 404);
        }

        $firstPayment = $payments->first();
        $existingRaw = json_decode((string) ($firstPayment?->raw_response ?? '{}'), true);
        $pollMeta = [
            'attempt' => (int) ($existingRaw['poll_attempt'] ?? 0),
            'checked_at' => now()->toDateTimeString(),
            'source' => (string) ($existingRaw['source'] ?? 'database-state'),
            'query_reference' => (string) ($existingRaw['query_reference'] ?? ''),
        ];

        if ($payments->every(fn(Payment $payment) => in_array(strtolower((string) $payment->status), ['completed', 'failed'], true))) {
            if ($payments->every(fn(Payment $payment) => strtolower((string) $payment->status) === 'completed')) {
                $this->provisionCompletedPayments((string) ($payments->first()->txn_id ?? $conversationId));
            }

            return response()->json([
                'status' => strtolower((string) $payments->first()->status),
                'conversation_id' => $conversationId,
                'txn_id' => $payments->first()->txn_id,
                'payment' => $payments->first(),
                'poll' => $pollMeta,
            ]);
        }

        $chargePayload = is_array($existingRaw) ? ($existingRaw['charge'] ?? null) : null;

        $queryReference = $this->resolveMpesaQueryReference($conversationId, $chargePayload);

        $vcl = new VclController($this->options);
        $queryResponse = $vcl->query([
            'input_QueryReference' => $queryReference,
            'input_Country' => 'LES',
            'input_ServiceProviderCode' => config('laravel-pesa.short_code'),
            'input_ThirdPartyConversationID' => $conversationId,
        ]);

        $normalizedStatus = $this->normalizeMpesaStatus($queryResponse, $firstPayment);
        $txnId = $queryResponse['output_OriginalTransactionID'] ?? null;
        $pollAttempt = max(1, (int) ($existingRaw['poll_attempt'] ?? 0) + 1);

        $updatePayload = [
            'status' => $normalizedStatus,
            'raw_response' => json_encode([
                'charge' => $chargePayload,
                'query' => $queryResponse,
                'source' => 'api-status-poll',
                'checked_at' => now()->toDateTimeString(),
                'poll_attempt' => $pollAttempt,
                'query_reference' => $queryReference,
            ]),
        ];

        if (!empty($txnId) && strtoupper((string) $txnId) !== 'N/A') {
            $updatePayload['txn_id'] = $txnId;
        }

        Payment::query()
            ->where('type', 'mpesa')
            ->where('conversation_id', $conversationId)
            ->update($updatePayload);

        $payment = Payment::query()
            ->where('type', 'mpesa')
            ->where('conversation_id', $conversationId)
            ->first();

        if ($normalizedStatus === 'completed' && !empty($payment?->txn_id)) {
            $this->provisionCompletedPayments((string) $payment->txn_id);
            SendPaymentInvoice::dispatchFor((string) $payment->txn_id, 'mpesa');
        }

        return response()->json([
            'status' => $normalizedStatus,
            'conversation_id' => $conversationId,
            'query_reference' => $queryReference,
            'txn_id' => $payment?->txn_id,
            'query' => $queryResponse,
            'payment' => $payment,
            'poll' => [
                'attempt' => $pollAttempt,
                'checked_at' => now()->toDateTimeString(),
                'source' => 'api-status-poll',
                'query_reference' => $queryReference,
            ],
        ]);
    }

    private function normalizeMpesaStatus(array $queryResponse, ?Payment $payment = null): string
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

        // These codes often appear before the transaction is fully indexed by QTS.
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

    private function resolveMpesaQueryReference(string $conversationId, ?array $chargePayload): string
    {
        $providerConversationId = (string) ($chargePayload['output_ConversationID'] ?? '');
        if ($providerConversationId !== '' && strtoupper($providerConversationId) !== 'N/A') {
            return $providerConversationId;
        }

        $providerTransactionId = (string) ($chargePayload['output_TransactionID'] ?? '');
        if ($providerTransactionId !== '' && strtoupper($providerTransactionId) !== 'N/A') {
            return $providerTransactionId;
        }

        return $conversationId;
    }

    public function b2c(Request $request)
    {
        Log::info("B2C route accessed.");
        Log::info("Options for VclController: ", $this->options);

        $number = $request->input('input_CustomerMSISDN');
        $mssid = '266' . $number;

        $vclService = new VclController($this->options);
        $response = $vclService->b2c([
            'input_Amount' => $request->input('input_Amount'),
            'input_Country' => 'LES',
            'input_Currency' => 'LSL',
            'input_CustomerMSISDN' => $mssid,
            'input_ServiceProviderCode' => config('laravel-pesa.short_code'),
            'input_ThirdPartyConversationID' => 'NID' . rand(),
            'input_TransactionReference' => 'nidptyltd',
            'input_PaymentItemsDesc' => $request->input('input_PaymentItemsDesc')
        ]);

        Log::info("Charge response: " . print_r($response, true));
        return response()->json($response);
    }

    public function b2b(Request $request)
    {
        Log::info("B2B route accessed.");
        Log::info("Options for VclController: ", $this->options);

        $number = $request->input('input_ReceiverPartyCode');
        $mssid =  $number;

        $vclService = new VclService($this->options);
        $response = $vclService->b2b([
            'input_Amount' => $request->input('input_Amount'),
            'input_Country' => 'LES',
            'input_Currency' => 'LSL',
            'input_PrimaryPartyCode' => config('laravel-pesa.short_code'),
            'input_ReceiverPartyCode' => $mssid,
            'input_ThirdPartyConversationID' => 'NID' . rand(),
            'input_TransactionReference' => 'nidptyltd',
            'input_PurchasedItemsDesc' => $request->input('input_PurchasedItemsDesc')
        ]);

        Log::info("Charge response: " . print_r($response, true));
        return response()->json($response);
    }

    public function reverse(Request $request)
    {
        Log::info("Reverse route accessed.");
        Log::info("Options for VclController: ", $this->options);

        $vclService = new VclService($this->options);

        $response = $vclService->reverse([
            'input_ReversalAmount' => $request->input('input_Amount'),
            'input_Country' => 'LES',
            'input_ServiceProviderCode' => config('laravel-pesa.short_code'),
            'input_ThirdPartyConversationID' => 'NID' . rand(),
            'input_TransactionReference' => 'nidptyltd',
            'input_TransactionID' => $request->input('input_TransactionID')
        ]);

        Log::info("Reverse response: " . print_r($response, true));
        return response()->json($response);
    }
    public function paypalPay(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'item_id' => 'nullable',
            'item_type' => 'nullable|string',
            'description' => 'nullable|string',
            'client' => 'nullable|string|in:web,mobile',
            'return_url' => 'nullable|string|max:2048',
            'cancel_url' => 'nullable|string|max:2048',
        ]);

        return $this->createPayPalPayment(
            $request->amount,
            $request->input('description', 'PayPal payment'),
            $request->input('item_id'),
            $request->input('item_type', 'music'),
            $request->input('return_url'),
            $request->input('cancel_url'),
            $request->input('client', 'web'),
            $request->input('service_type'),
            $request->input('title')
        );
    }

    public function createMusicPayPalOrder(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
            'item_id' => 'nullable',
            'item_type' => 'nullable|string',
            'description' => 'nullable|string',
            'client' => 'nullable|string|in:web,mobile',
            'return_url' => 'nullable|string|max:2048',
            'cancel_url' => 'nullable|string|max:2048',
        ]);

        return $this->createPayPalPayment(
            $request->amount,
            $request->input('description', 'Music purchase'),
            $request->input('item_id'),
            $request->input('item_type', 'music'),
            $request->input('return_url'),
            $request->input('cancel_url'),
            $request->input('client', 'web')
        );
    }

    public function createServicePayPalOrder(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
            'item_id' => 'nullable',
            'item_type' => 'nullable|string',
            'description' => 'nullable|string',
            'client' => 'nullable|string|in:web,mobile',
            'return_url' => 'nullable|string|max:2048',
            'cancel_url' => 'nullable|string|max:2048',
        ]);

        return $this->createPayPalPayment(
            $request->amount,
            $request->input('description', 'Service purchase'),
            $request->input('item_id'),
            $request->input('item_type', 'service'),
            $request->input('return_url'),
            $request->input('cancel_url'),
            $request->input('client', 'web'),
            $request->input('service_type'),
            $request->input('title')
        );
    }

    public function captureMusicPayPalOrder(Request $request)
    {
        return $this->capturePayPalPayment($request);
    }

    public function captureServicePayPalOrder(Request $request)
    {
        return $this->capturePayPalPayment($request);
    }

    private function createPayPalPayment($amount, $description = 'PayPal payment', $itemId = null, $itemType = null, $returnUrl = null, $cancelUrl = null, $client = 'web', $serviceType = null, $title = null)
    {
        $amountZar = (float) $amount;
        $conversion = $this->convertZarToUsd($amountZar);
        $amountUsd = $conversion['usd_amount'];
        $isMobile = strtolower((string) $client) === 'mobile';
        $mobileRedirectUrl = (string) config('app.frontend_mobile_url', 'com.streama.app://paypal-callback');
        $successRedirectUrl = $isMobile
            ? $mobileRedirectUrl
            : ($this->sanitizeClientRedirectUrl($returnUrl) ?? $this->defaultPaypalResultUrl($client));
        $cancelRedirectUrl = $isMobile
            ? $mobileRedirectUrl
            : ($this->sanitizeClientRedirectUrl($cancelUrl) ?? $successRedirectUrl);

        $paypal = app(\App\Services\PayPalService::class);
        $order = $paypal->createOrder($amountUsd, 'USD', $successRedirectUrl, $cancelRedirectUrl, $client);

        $approvalLink = collect($order['links'] ?? [])
            ->firstWhere('rel', 'approve')['href'] ?? null;

        $itemType = strtolower((string) $itemType);
        $itemIds = $this->extractPaymentItemIds($itemId, $itemType);

        $payments = $this->createPaymentRows(
            $itemIds,
            $itemType,
            [
                'user_id' => Auth::id(),
                'amount' => $amountZar,
                'txn_id' => $order['id'] ?? null,
                'type' => 'paypal',
                'status' => 'pending',
                'description' => $description,
                'service_type' => $serviceType,
                'title' => $title ?: $description,
                'raw_response' => json_encode([
                    'paypal_order' => $order,
                    'conversion' => $conversion,
                    'redirects' => [
                        'return_url' => $successRedirectUrl,
                        'cancel_url' => $cancelRedirectUrl,
                    ],
                ]),
            ]
        );

        return response()->json([
            'success' => true,
            'order_id' => $order['id'] ?? null,
            'approval_url' => $approvalLink,
            'amount' => [
                'zar' => round($amountZar, 2),
                'usd' => $amountUsd,
                'rate' => $conversion['rate'],
                'source' => $conversion['source'],
            ],
            'payment' => $payments->first(),
            'payments' => $payments,
        ]);
    }

    private function capturePayPalPayment(Request $request)
    {
        $orderId = $request->input('order_id');
        $payerId = $request->input('payer_id');

        if (!$orderId) {
            return response()->json([
                'success' => false,
                'message' => 'Missing order_id',
            ], 422);
        }

        $payments = Payment::where('txn_id', $orderId)
            ->where('type', 'paypal')
            ->get();

        if ($payments->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No order is found based on order_id',
            ], 404);
        }

        if ($payments->every(fn(Payment $payment) => $payment->status === 'completed')) {
            $this->provisionCompletedPayments((string) $orderId);

            return response()->json([
                'success' => true,
                'status' => 'completed',
                'message' => 'Already processed',
                'txn' => $orderId,
            ]);
        }

        try {
            $paypal = app(\App\Services\PayPalService::class);
            $result = $paypal->captureOrder($orderId);

            $paypalStatus = strtoupper($result['status'] ?? 'FAILED');
            $status = in_array($paypalStatus, ['COMPLETED', 'APPROVED']) ? 'completed' : 'failed';
            Payment::where('txn_id', $orderId)
                ->where('type', 'paypal')
                ->update([
                    'status' => $status,
                    'conversation_id' => $payerId ?: $orderId,
                    'raw_response' => json_encode($result),
                ]);

            if ($status === 'completed') {
                $this->provisionCompletedPayments((string) $orderId);
                SendPaymentInvoice::dispatchFor((string) $orderId, 'paypal');
            }

            return response()->json([
                'success' => $status === 'completed',
                'status' => $status,
                'message' => $status === 'completed' ? 'Payment completed' : 'Payment failed',
                'txn' => $orderId,
            ]);
        } catch (\Throwable $e) {
            Log::error('PayPal capture exception', [
                'order_id' => $orderId,
                'error' => $e->getMessage(),
            ]);

            Payment::where('txn_id', $orderId)
                ->where('type', 'paypal')
                ->update([
                    'status' => 'failed',
                    'raw_response' => json_encode(['error' => $e->getMessage()]),
                ]);

            return response()->json([
                'success' => false,
                'message' => 'Capture failed',
            ], 500);
        }
    }
    public function paypalSuccess(Request $request, PayPalService $paypal)
    {
        $orderId = $request->query('token');
        $payerId = $request->query('PayerID');

        if (!$orderId) {
            Log::warning('PayPal Success: missing token');
            return $this->redirectToClientResult($request, 'failed', 'missing_token');
        }

        Log::info('PayPal Success Callback', [
            'order_id' => $orderId,
            'payer' => $payerId
        ]);

        $payment = Payment::where('txn_id', $orderId)
            ->where('type', 'paypal')
            ->first();

        if (!$payment) {
            Log::error('PayPal payment not found', ['order_id' => $orderId]);
            return $this->redirectToClientResult($request, 'failed', $orderId);
        }

        // 🔒 Idempotency: prevent double processing
        if ($payment->status === 'completed') {
            return $this->redirectToClientResult($request, 'completed', $orderId);
        }

        try {
            $result = $paypal->captureOrder($orderId);

            Log::info('PayPal Capture Result', $result);

            $paypalStatus = strtoupper($result['status'] ?? 'FAILED');

            // ✔ Accept both valid success states
            $isSuccess = in_array($paypalStatus, [
                'COMPLETED',
                'APPROVED'
            ]);

            $status = $isSuccess ? 'completed' : 'failed';

            Payment::where('txn_id', $orderId)
                ->where('type', 'paypal')
                ->update([
                    'status' => $status,
                    'conversation_id' => $payerId ?: $orderId,
                    'raw_response' => json_encode($result)
                ]);

            if ($status === 'completed') {
                $this->provisionCompletedPayments((string) $orderId);
                SendPaymentInvoice::dispatchFor((string) $orderId, 'paypal');
            }
        } catch (\Throwable $e) {

            Log::error('PayPal capture exception', [
                'order_id' => $orderId,
                'error' => $e->getMessage()
            ]);

            Payment::where('txn_id', $orderId)
                ->where('type', 'paypal')
                ->update([
                    'status' => 'failed',
                    'raw_response' => json_encode([
                        'error' => $e->getMessage()
                    ])
                ]);

            return $this->redirectToClientResult($request, 'failed', $orderId);
        }

        return $this->redirectToClientResult($request, $status, $orderId);
    }

    public function paypalCancel(Request $request)
    {
        return $this->redirectToClientResult($request, 'cancelled');
    }

    private function defaultPaypalResultUrl(string $client = 'web'): string
    {
        if (strtolower($client) === 'mobile') {
            return (string) config('app.frontend_mobile_url', 'com.streama.app://paypal-callback');
        }

        $frontend = rtrim((string) config('app.frontend_web_url', config('app.frontend_url', 'http://localhost:9000')), '/');

        return $frontend . '/paypal/result';
    }

    private function sanitizeClientRedirectUrl($url): ?string
    {
        if (!is_string($url)) {
            return null;
        }

        $value = trim($url);
        if ($value === '' || strlen($value) > 2048) {
            return null;
        }

        $parts = parse_url($value);
        if ($parts === false || empty($parts['scheme'])) {
            return null;
        }

        $scheme = strtolower((string) $parts['scheme']);
        if (in_array($scheme, ['http', 'https'], true)) {
            $host = strtolower((string) ($parts['host'] ?? ''));
            $allowedHosts = $this->allowedPaypalRedirectHosts();

            if ($host !== '' && in_array($host, $allowedHosts, true)) {
                return $value;
            }

            return null;
        }

        // Allow custom schemes for Capacitor deep-link handoff (e.g. myapp://paypal/result).
        if (preg_match('/^[a-z][a-z0-9+.-]*$/', $scheme) === 1) {
            return $value;
        }

        return null;
    }

    private function allowedPaypalRedirectHosts(): array
    {
        $hosts = [];

        $frontendHost = strtolower((string) parse_url((string) config('app.frontend_url', ''), PHP_URL_HOST));
        if ($frontendHost !== '') {
            $hosts[] = $frontendHost;
        }

        $frontendWebHost = strtolower((string) parse_url((string) config('app.frontend_web_url', ''), PHP_URL_HOST));
        if ($frontendWebHost !== '') {
            $hosts[] = $frontendWebHost;
        }

        $extraHosts = array_filter(array_map(
            static fn($host) => strtolower(trim((string) $host)),
            explode(',', (string) env('PAYPAL_REDIRECT_ALLOWED_HOSTS', ''))
        ));

        return array_values(array_unique(array_merge($hosts, $extraHosts)));
    }

    private function redirectToClientResult(Request $request, string $status, ?string $txn = null)
    {
        $client = strtolower((string) $request->query('client', 'web'));

        if ($client === 'mobile') {
            return response()->view('payment.result', [
                'status' => $status,
                'txnId' => $txn,
            ]);
        }

        $redirectBase = $this->sanitizeClientRedirectUrl($request->query('redirect_uri'))
            ?? $this->defaultPaypalResultUrl($client);

        $query = ['status' => $status];
        if (!empty($txn)) {
            $query['txn'] = $txn;
        }

        $target = $this->appendQueryToUrl($redirectBase, $query);

        return redirect()->away($target);
    }

    private function appendQueryToUrl(string $url, array $query): string
    {
        if (empty($query)) {
            return $url;
        }

        $queryString = http_build_query($query);

        if (str_contains($url, '#')) {
            [$base, $fragment] = explode('#', $url, 2);
            $fragment .= str_contains($fragment, '?') ? '&' : '?';

            return $base . '#' . $fragment . $queryString;
        }

        return $url . (str_contains($url, '?') ? '&' : '?') . $queryString;
    }

    private function convertZarToUsd(float $amountZar): array
    {
        $apiKey = config('payments.currency_api_key');
        $baseCurrency = 'ZAR';
        $targetCurrency = 'USD';

        try {
            $url = "https://open.er-api.com/v6/latest/{$baseCurrency}";
            if (!empty($apiKey)) {
                $url .= '?apikey=' . urlencode($apiKey);
            }

            $response = Http::timeout(10)->acceptJson()->get($url);
            $data = $response->json();

            if ($response->successful() && isset($data['rates'][$targetCurrency])) {
                $rate = (float) $data['rates'][$targetCurrency];
                $usdAmount = round(max(0.01, $amountZar * $rate), 2);

                return [
                    'rate' => $rate,
                    'usd_amount' => $usdAmount,
                    'source' => 'open.er-api.com',
                ];
            }
        } catch (\Throwable $e) {
            Log::warning('Currency conversion failed, using fallback rate', [
                'error' => $e->getMessage(),
                'amount_zar' => $amountZar,
            ]);
        }

        // Safe fallback keeps checkout working even if FX API is unavailable.
        $fallbackRate = 0.055;

        return [
            'rate' => $fallbackRate,
            'usd_amount' => round(max(0.01, $amountZar * $fallbackRate), 2),
            'source' => 'fallback',
        ];
    }

    public function capture(Request $request, PayPalService $paypal)
    {
        $orderId = $request->input('order_id');

        if (!$orderId) {
            return response()->json([
                'error' => 'Missing order_id'
            ], 422);
        }

        $payments = Payment::where('txn_id', $orderId)
            ->where('type', 'paypal')
            ->get();

        if ($payments->isEmpty()) {
            return response()->json([
                'error' => 'Payment not found'
            ], 404);
        }

        // Prevent duplicate processing
        if ($payments->every(fn(Payment $payment) => $payment->status === 'completed')) {
            return response()->json([
                'status' => 'completed',
                'message' => 'Already processed',
                'txn' => $orderId
            ]);
        }

        try {
            $result = $paypal->captureOrder($orderId);

            // Log full response for debugging
            Log::info('PayPal Capture Response', $result);

            $paypalStatus = strtoupper($result['status'] ?? 'FAILED');

            $status = in_array($paypalStatus, ['COMPLETED', 'APPROVED'])
                ? 'completed'
                : 'failed';

            Payment::where('txn_id', $orderId)
                ->where('type', 'paypal')
                ->update([
                    'status' => $status,
                    'conversation_id' => $orderId,
                    'raw_response' => json_encode($result)
                ]);

            if ($status === 'completed') {
                SendPaymentInvoice::dispatchFor((string) $orderId, 'paypal');
            }

            return response()->json([
                'status' => $status,
                'txn' => $orderId
            ]);
        } catch (\Throwable $e) {

            Log::error('PayPal capture exception', [
                'order_id' => $orderId,
                'error' => $e->getMessage()
            ]);

            Payment::where('txn_id', $orderId)
                ->where('type', 'paypal')
                ->update([
                    'status' => 'failed',
                    'raw_response' => json_encode([
                        'error' => $e->getMessage()
                    ])
                ]);

            return response()->json([
                'status' => 'failed',
                'error' => 'Capture failed'
            ], 500);
        }
    }

    private function createPaymentRows(array $itemIds, string $itemType, array $attributes)
    {
        if (!in_array($itemType, ['music', 'service'], true) || ($itemType === 'music' && count($itemIds) === 0)) {
            throw new \InvalidArgumentException('A valid music or service item ID is required.');
        }

        if ($itemType === 'service' && count($itemIds) === 0) {
            $itemIds = [null];
        }

        $count = count($itemIds);
        $amount = (float) ($attributes['amount'] ?? 0);
        $splitAmount = $count > 0 ? round($amount / $count, 2) : $amount;

        return collect($itemIds)->map(function ($itemId) use ($attributes, $splitAmount, $itemType) {
            $sellerId = $itemType === 'music'
                ? Music::query()->whereKey($itemId)->with('release')->first()?->release?->user_id
                : null;

            return Payment::create(array_merge($attributes, [
                'music_id' => $itemType === 'music' ? $itemId : null,
                'service_id' => $itemType === 'service' ? $itemId : null,
                'seller_id' => $sellerId,
                'amount' => $splitAmount,
            ]));
        });
    }

    private function extractPaymentItemIds($itemId, string $itemType): array
    {
        if (!in_array($itemType, ['music', 'service'], true)) {
            return [];
        }

        if (is_array($itemId)) {
            return array_values(array_filter(array_map('intval', $itemId), fn(int $id) => $id > 0));
        }

        if (is_string($itemId) && str_contains($itemId, ',')) {
            return array_values(array_filter(
                array_map('intval', array_map('trim', explode(',', $itemId))),
                fn(int $id) => $id > 0
            ));
        }

        if (is_numeric($itemId) && (int) $itemId > 0) {
            return [(int) $itemId];
        }

        return [];
    }
}
