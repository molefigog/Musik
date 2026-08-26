<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class CpayService
{
    // public function makePayment(string $msisdn, float $amount, string $description)
    // {
    //     Log::info('CPay Proxy Payment Request', [
    //         'msisdn' => $msisdn,
    //         'amount' => $amount,
    //         'description' => $description,
    //         'url' => env('CPAY_PROXY_URL') . '/payment.php'
    //     ]);


    //     try {

    //         $response = Http::asForm()
    //             ->withHeaders([
    //                 'X-API-Key' => env('CPAY_PROXY_KEY')
    //             ])
    //             ->post(
    //                 env('CPAY_PROXY_URL') . '/payment.php',
    //                 [
    //                     'msisdn' => $msisdn,
    //                     'amount' => $amount,
    //                     'description' => $description
    //                 ]
    //             );


    //         Log::info('CPay Proxy Payment Response', [
    //             'status' => $response->status(),
    //             'response' => $response->json()
    //         ]);


    //         return $response->json();
    //     } catch (\Exception $e) {


    //         Log::error('CPay Proxy Payment Error', [
    //             'message' => $e->getMessage(),
    //             'msisdn' => $msisdn,
    //             'amount' => $amount
    //         ]);


    //         return [
    //             'success' => false,
    //             'message' => $e->getMessage()
    //         ];
    //     }
    // }

    // public function confirmPayment(
    //     string $transactionId,
    //     string $msisdn,
    //     float $amount,
    //     string $otp
    // ) {

    //     $response = Http::asForm()
    //         ->withHeaders([
    //             'X-API-Key' => env('CPAY_PROXY_KEY')
    //         ])
    //         ->post(
    //             env('CPAY_PROXY_URL') . '/confirm.php',
    //             [
    //                 'transactionId' => $transactionId,
    //                 'msisdn' => $msisdn,
    //                 'amount' => $amount,
    //                 'otp' => $otp
    //             ]
    //         );


    //     $json = $response->json();


    //     return [

    //         'raw' => [
    //             'return' => $json['response']['return'] ?? []
    //         ],

    //         'success' => ($json['response']['return']['StatusCode'] ?? null)
    //             === '200'

    //     ];
    // }

    // public function cardPayment(array $payload)
    // {

    //     $response = Http::asForm()
    //         ->withHeaders([
    //             'X-API-Key' => env('CPAY_PROXY_KEY')
    //         ])
    //         ->post(
    //             env('CPAY_PROXY_URL') . '/card.php',
    //             $payload
    //         );


    //     $json = $response->json();


    //     return [

    //         'type' => $json['type'] ?? 'json',

    //         'html' => $json['html'] ?? null,

    //         'redirect_url' => $json['redirect_url'] ?? null,

    //         'raw' => $json

    //     ];
    // }
    protected string $clientCode;
    protected string $apiKey;
    protected string $secret;
    protected Client $http;

    public function __construct()
    {
        $this->clientCode = config('payments.cpay_code');
        $this->apiKey     = config('payments.cpay_api');
        $this->secret     = config('payments.cpay_secret');

        $this->http = new Client([
            'timeout' => 20,
            'headers' => [
                'Accept'        => 'application/json',
                'Content-Type'  => 'application/json-patch+json',
                'Authorization' => $this->apiKey,
            ],
        ]);
    }

    public function makePayment(string $msisdn, float $amount, string $description = 'payment')
    {
        $msisdn = $this->clean($msisdn);
        $msisdn = preg_replace('/\D/', '', $msisdn);
        $amount = number_format($amount, 2, '.', '');
        $description = $this->clean($description);
        $sequence = $this->nextSequence();
        $transactionId = 'MERCHANT_TXN_' . now()->format('Ymd') . $sequence;
        $salt = $transactionId . $this->clientCode . $amount . $msisdn;
        $checksum = hash_hmac('sha256', $salt, $this->secret);

        Log::info('CPay Request', [
            'transactionId' => $transactionId,
            'clientCode'    => $this->clientCode,
            'amount'        => $amount,
            'msisdn'        => $msisdn,
        ]);

        $payload = [
            "transactionRequest" => [
                "extTransactionId" => $transactionId,
                "clientCode"       => $this->clientCode,
                "msisdn"           => $msisdn,
                "amount"           => $amount,
                "shortDescription" => $description,
                "checksum"         => $checksum,
                "currency"         => "LSL",
                "otpMedium"        => "sms",
                "redirectUrl"      => ""
            ]
        ];

        try {
            $response = $this->http->post(
                'https://cpay-uat-env.chaperone.co.ls:5100/api/cpaypayments/payment',
                [
                    'body' => json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                    // 'verify' => false,
                ]
            );

            $body = $response->getBody()->getContents();
            $data = json_decode($body, true);

            Log::info('CPay Response', [
                'transactionId' => $transactionId,
                'statusCode'    => $data['return']['StatusCode'] ?? null,
                'reasonCode'    => $data['return']['ReasonCode'] ?? null,
                'description'   => $data['return']['Description'] ?? null,
                'raw'           => $data,
            ]);

            return $data;
        } catch (\GuzzleHttp\Exception\ClientException $e) {

            $errorBody = $e->getResponse()
                ? (string) $e->getResponse()->getBody()
                : null;

            Log::error('CPay Client Error', [
                'transactionId' => $transactionId,
                'error' => $e->getMessage(),
                'response' => $errorBody,
            ]);

            throw $e;
        } catch (\Exception $e) {

            Log::error('CPay General Error', [
                'transactionId' => $transactionId,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    public function confirmPayment(string $transactionId, string $msisdn, float $amount, string $otp)
    {
        $msisdn = $this->clean($msisdn);
        $amount = number_format($amount, 2, '.', '');
        $salt = $transactionId . $this->clientCode . $amount . $msisdn . $otp;
        $checksum = hash_hmac('sha256', $salt, $this->secret);

        $payload = [
            "transactionRequest" => [
                "extTransactionId" => $transactionId,
                "clientCode"       => $this->clientCode,
                "msisdn"           => $msisdn,
                "amount"           => $amount,
                "otp"              => $otp,
                "checksum"         => $checksum,
                "currency"         => "LSL"
            ]
        ];
        try {
            $response = $this->http->post(
                'https://cpay-uat-env.chaperone.co.ls:5100/api/cpaypayments/confirm',
                [
                    'body' => json_encode($payload, JSON_UNESCAPED_UNICODE),
                    'verify' => false,
                    'headers' => [
                        'Accept'        => 'application/json',
                        'Content-Type'  => 'application/json-patch+json',
                        'Authorization' => $this->apiKey,
                    ],
                ]
            );
            $data = json_decode($response->getBody()->getContents(), true);
            Log::info('CPay Confirm Response', [
                'transactionId' => $transactionId,
                'statusCode' => $data['statusCode'] ?? null,
                'status' => $data['paymentRequestStatus'] ?? null,
                'message' => $data['description'] ?? null,
                'raw' => $data,
            ]);
            return [
                'success' => in_array(($data['statusCode'] ?? ''), ['0000', '200']),
                'status' => $data['paymentRequestStatus'] ?? null,
                'message' => $data['description'] ?? null,
                'transaction_id' => $data['extTransactionId'] ?? null,
                'cpay_id' => $data['cPayTransactionId'] ?? null,
                'raw' => $data
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    public function cardPayment(array $data)
    {
        $msisdn = preg_replace('/\D/', '', $this->clean($data['msisdn']));
        $amount = number_format($data['amount'], 2, '.', '');
        $transactionId = $data['extTransactionId']  ?? 'CARD_TXN_' . now()->format('YmdHis');
        $salt = $transactionId . $this->clientCode . $amount . $msisdn;
        $checksum = hash_hmac('sha256', $salt, $this->secret);

        $payload = [
            "transactionRequest" => [
                "extTransactionId" => $transactionId,
                "clientCode"       => $this->clientCode,
                "msisdn"           => $msisdn,
                "amount"           => $amount,
                "checksum"         => $checksum,
                "currency"         => $data['currency'] ?? "LSL",
                "redirectUrl"      => $data['redirectUrl'] ?? "http://gw-ent.co.za/payment/callback",
                "shortDescription" => "",
                "otpMedium"        => "sms"
            ]
        ];

        try {
            $url = config('payments.cpay_url')
                . '/api/cpaypayments/payment?cardPayment=true&rememberMe=false&email='
                . urlencode($data['email'] ?? 'molefigw@gmail.com');

            $response = $this->http->post($url, [
                'headers' => [
                    'Accept' => 'text/plain',
                    'Content-Type' => 'application/json-patch+json',
                    'Authorization' => $this->apiKey,
                ],
                'body' => json_encode($payload),
                'verify' => false,
                'http_errors' => false,
            ]);

            $body = $response->getBody()->getContents();
            $redirectUrl = $response->getHeaderLine('redirecturl');

            Log::info('CPay Raw Response', [
                'body' => $body,
                'redirect' => $redirectUrl
            ]);

            if (str_contains($body, '<iframe')) {
                return [
                    'type' => 'html',
                    'html' => $body,
                    'redirect_url' => $redirectUrl
                ];
            }

            $decoded = json_decode($body, true);

            return [
                'type' => 'json',
                'status_code' => $decoded['StatusCode'] ?? null,
                'message' => $decoded['Description'] ?? null,
                'redirect_url' => $redirectUrl
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    public function checkStatus(string $transactionId)
    {
        $url = config('payments.cpay_url') . '/api/cpaypayments/transaction-status?' . http_build_query([
            'requestReference' => $transactionId,
            'dateTime' => now()->format('Y-m-d')
        ]);

        try {
            $response = $this->http->get($url, ['verify' => false]);

            $data = json_decode($response->getBody()->getContents(), true);

            Log::info('CPay Status Check', [
                'transactionId' => $transactionId,
                'response' => $data
            ]);

            return $data;
        } catch (\Exception $e) {
            Log::error('CPay Status Error', [
                'transactionId' => $transactionId,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    private function clean($value): string
    {
        return trim(mb_convert_encoding((string) $value, 'UTF-8', 'UTF-8'));
    }


    protected function nextSequence(): int
    {
        $file = storage_path('app/cpay-sequence.txt');

        $fp = fopen($file, 'c+');
        flock($fp, LOCK_EX);

        $current = (int) trim(stream_get_contents($fp));
        $next = $current + 1;

        ftruncate($fp, 0);
        rewind($fp);
        fwrite($fp, (string) $next);

        fflush($fp);
        flock($fp, LOCK_UN);
        fclose($fp);

        return $next;
    }

    public function walletTopupAdvance(
        string $msisdn,
        float $amount,
        array $recipientKyc,
        ?string $destinationOperator = null,
        string $description = 'Wallet top-up'
    ) {

        $msisdn = preg_replace('/\D/', '', $msisdn);
        $amount = number_format($amount, 2, '.', '');
        $transactionId = 'MERCHANT_TXN_' .
            now()->format('YmdHis');

        $salt = $transactionId . $this->clientCode . $amount . $msisdn;
        $checksum = hash_hmac('sha256', $salt, $this->secret);

        $payload = [
            "transactionRequest" => [
                "transactionRequest" => [

                    "extTransactionId" => $transactionId,
                    "clientCode"       => $this->clientCode,
                    "msisdn"           => $msisdn,
                    "amount"           => $amount,
                    "shortDescription" => $description,
                    "checksum"         => $checksum,
                    "currency"         => "LSL",
                    "redirectUrl"      => "",
                    "destinationOperator" => $destinationOperator,
                    "additionalData" => [
                        "recipientKyc" => $recipientKyc
                    ]
                ]
            ]
        ];
        $payload['transactionRequest'] = array_filter(
            $payload['transactionRequest'],
            fn($value) => !is_null($value)
        );
        Log::info('CPAY Wallet Topup Request', ['payload' => $payload]);

        try {

            $response = $this->http->post(
                'https://cpay-uat-env.chaperone.co.ls:5100/api/disbursements/wallet-topup-advance',
                [
                    'headers' => [
                        'Content-Type' => 'application/json'
                    ],
                    'body' => json_encode(
                        $payload,
                        JSON_UNESCAPED_UNICODE |
                            JSON_UNESCAPED_SLASHES
                    ),
                    'verify' => false,
                ]
            );

            $body = $response
                ->getBody()
                ->getContents();

            $data = json_decode($body, true);

            Log::info('CPAY Wallet Topup Response', [
                'response' => $data
            ]);

            return $data;
        } catch (\Exception $e) {

            Log::error('CPAY Wallet Topup Error', [
                'message' => $e->getMessage()
            ]);

            throw $e;
        }
    }
}
