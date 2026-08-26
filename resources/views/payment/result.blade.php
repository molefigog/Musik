<!DOCTYPE html>
<html>
<head>
    <title>Payment Result</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f7fb;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
        }
        .card {
            background: white;
            padding: 40px;
            border-radius: 16px;
            text-align: center;
            width: 90%;
            max-width: 420px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        .success { color: #21ba45; }
        .failed { color: #c10015; }
        .close-message {
            margin-top: 20px;
            color: #4b5563;
            line-height: 1.5;
        }
    </style>
</head>
<body>

<div class="card">

    <div style="font-size:60px;">
        {{ $status === 'completed' ? '✅' : '❌' }}
    </div>
    <h2 class="{{ $status === 'completed' ? 'success' : 'failed' }}">
        {{ $status === 'completed' ? 'Payment Successful' : ($status === 'cancelled' ? 'Payment Cancelled' : 'Payment Failed') }}
    </h2>
    @if (!empty($txnId))
        <p>Transaction ID: <b>{{ $txnId }}</b></p>
    @endif
    <p class="close-message">Close this browser to return to the app.</p>

</div>


</body>
</html>
