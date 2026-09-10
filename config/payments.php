<?php
//payments.php configuration file

return [

    /*
    |--------------------------------------------------------------------------
    | Mpesa API Pubic Key
    |--------------------------------------------------------------------------
    */
    'public_key' => env('PESA_PUBLIC_KEY'),
    /*
    |--------------------------------------------------------------------------
    | Mpesa API Secret Key
    |--------------------------------------------------------------------------
    */
    'api_key' => env('PESA_API_KEY'),
    /*
    |--------------------------------------------------------------------------
    | Environment to use
    |--------------------------------------------------------------------------
    */
    'env' => env('PESA_ENV'),
    /*
    |--------------------------------------------------------------------------
    | Environment to use
    |--------------------------------------------------------------------------
    */
    'short_code' => env('PESA_SC'),

    'client_id' => env('PAYPAL_CLIENT_ID'),
    'secret' => env('PAYPAL_SECRET'),
    'mode' => env('PAYPAL_MODE', 'sandbox'),
    'paypal_fx_markup' => env('PAYPAL_FX_MARKUP', 0.04),

    'currency_api_key' => env('CURRENCY_API_KEY'),

    'cpay_api' => env('CPAY_API'),
    'cpay_secret' => env('CPAY_SECRET'),
    'cpay_code' => env('CPAY_CODE'),
    'cpay_url' => env('CPAY_URL'),

    'sms_api' => env('SMSapiKey'),
    'sms_secret' => env('SMSapiSecret'),

    'ecocash_merchant_id' => env('ECOCASH_MERCHANT_ID'),
    'ecocash_merchant_name' => env('ECOCASH_MERCHANT_NAME'),
    'ecocash_token' => env('ECOCASH_TOKEN'),
];
