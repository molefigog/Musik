<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PayPalService
{
    private $baseUrl;
    private $clientId;
    private $secret;

    public function __construct()
    {
        $this->baseUrl = config('payments.mode', env('PAYPAL_MODE', 'sandbox')) === 'live'
            ? 'https://api-m.paypal.com'
            : 'https://api-m.sandbox.paypal.com';

        $this->clientId = config('payments.client_id');
        $this->secret = config('payments.secret');
    }

    private function getToken()
    {
        $response = Http::asForm()->withBasicAuth(
            $this->clientId,
            $this->secret
        )->post($this->baseUrl . '/v1/oauth2/token', [
            'grant_type' => 'client_credentials'
        ]);

        return $response->json()['access_token'];
    }

    public function createOrder($amount, $currency = 'USD', $returnUrl = null, $cancelUrl = null, $client = 'web')
    {
        $token = $this->getToken();
        $backendUrl = rtrim(config('app.url') ?? env('APP_URL', 'http://localhost:8000'), '/');

        $frontendWebUrl = rtrim((string) config('app.frontend_web_url', config('app.frontend_url', 'http://localhost:9000')), '/');
        $frontendMobileUrl = (string) config('app.frontend_mobile_url', 'com.streama.app://paypal-callback');
        $defaultTarget = strtolower((string) $client) === 'mobile'
            ? $frontendMobileUrl
            : $frontendWebUrl . '/paypal/result';

        $returnTarget = $returnUrl ?: $defaultTarget;
        $cancelTarget = $cancelUrl ?: $returnTarget;
        $payload = [
            'intent' => 'CAPTURE',
            'purchase_units' => [
                [
                    'amount' => [
                        'currency_code' => $currency,
                        'value' => (string) number_format($amount, 2, '.', '')
                    ]
                ]
            ],
            'application_context' => [
                'return_url' => $backendUrl . '/api/paypal/success?' . http_build_query([
                    'redirect_uri' => $returnTarget,
                    'client' => $client,
                ]),
                'cancel_url' => $backendUrl . '/api/paypal/cancel?' . http_build_query([
                    'redirect_uri' => $cancelTarget,
                    'client' => $client,
                ]),
                'landing_page' => 'BILLING',
                'user_action' => 'PAY_NOW'
            ]
        ];

        Log::info('PayPal Create Order Payload', $payload);

        $response = Http::withToken($token)
            ->acceptJson()
            ->asJson()
            ->post($this->baseUrl . '/v2/checkout/orders', $payload);

        Log::info('PayPal Create Order Response', $response->json());

        return $response->json();
    }

    public function captureOrder($orderId)
    {
        $token = $this->getToken();

        $response = Http::withToken($token)
            ->acceptJson()
            ->withHeaders([
                'Content-Type' => 'application/json'
            ])
            ->post(
                $this->baseUrl . "/v2/checkout/orders/{$orderId}/capture",
                new \stdClass() // 🔥 IMPORTANT: ensures valid JSON {}
            );

        return $response->json();
    }
}
