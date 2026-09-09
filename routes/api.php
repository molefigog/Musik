<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PaymentsController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\MusicController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\GenreController;
use App\Http\Controllers\Api\ReleaseController;
use App\Http\Controllers\Api\GenresMusicController;
use App\Http\Controllers\Api\ReleasesMusicController;
use App\Http\Controllers\Api\UsersPaymentController;
use App\Http\Controllers\Api\UsersReleaseController;
use App\Http\Controllers\Api\SessionController;
use App\Http\Controllers\Api\WalletTopupController;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Api\TaskController;
use App\Http\Controllers\Api\Admin\TaskController as AdminTaskController;
use App\Http\Controllers\Api\DownloadController;
use App\Http\Controllers\Api\FcmTokenController;
use App\Http\Controllers\Api\UserNotificationController;
use App\Http\Controllers\Api\PaymentGatewayController;
use App\Http\Controllers\Api\ReleaseWorkflowController;


Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::middleware('auth:sanctum')->post('/v1/cpay/pay', [PaymentsController::class, 'makePayment']);
Route::middleware('auth:sanctum')->post('/v1/cpay/confirm', [PaymentsController::class, 'confirmPayment']);
Route::middleware('auth:sanctum')->post('/v1/cpay/card', [PaymentsController::class, 'processCard']);
Route::middleware('auth:sanctum')->post('/v1/payments/mpesa/music',  [PaymentsController::class, 'charge']);
Route::middleware('auth:sanctum')->post('/v1/payments/mpesa/services',  [PaymentsController::class, 'chargeServices']);
Route::middleware('auth:sanctum')->get('/v1/payments/mpesa/status',  [PaymentsController::class, 'mpesaStatus']);
Route::middleware('auth:sanctum')->get('/v1/payment-gateways', [PaymentGatewayController::class, 'index']);

Route::post('/login', [AuthController::class, 'login'])->name('api.login');
Route::post('/register', [AuthController::class, 'register'])->name('api.register');
Route::middleware('auth:sanctum')->get('/me', [AuthController::class, 'me']);
Route::middleware('auth:sanctum')->put('/update-profile', [AuthController::class, 'updateProfile']);
Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);
Route::middleware('auth:sanctum')->post('/fcm-token', [FcmTokenController::class, 'store']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/all-music', [MusicController::class, 'index'])->name('all-music.index');
    Route::post('/all-music', [MusicController::class, 'store'])->name('all-music.store');
    Route::get('/all-music/{music}', [MusicController::class, 'show',])->name('all-music.show');
    Route::put('/all-music/{music}', [MusicController::class, 'update',])->name('all-music.update');
    Route::delete('/all-music/{music}', [MusicController::class, 'destroy',])->name('all-music.destroy');
    Route::post('/track-upload', [MusicController::class, 'temporaryUpload'])->name('track-upload');
    Route::patch('/update-track', [MusicController::class, 'updateTrack'])->name('update-track');
    Route::post('/revert-upload', [MusicController::class, 'revertUpload'])->name('revert-upload');
    Route::get('/all-music', [MusicController::class, 'index'])->name('all-music.index');
    Route::post('/all-music', [MusicController::class, 'store'])->name('all-music.store');
    Route::get('/all-music/{music}', [MusicController::class, 'show',])->name('all-music.show');
    Route::put('/all-music/{music}', [MusicController::class, 'update',])->name('all-music.update');
    Route::delete('/all-music/{music}', [MusicController::class, 'destroy',])->name('all-music.destroy');
    Route::get('/releases/{release}/all-music', [ReleasesMusicController::class, 'index',])->name('releases.all-music.index');
    Route::post('/releases/{release}/all-music', [ReleasesMusicController::class, 'store',])->name('releases.all-music.store');
    Route::get('/releases', [ReleaseController::class, 'index'])->name('releases.index');
    Route::post('/releases', [ReleaseController::class, 'store'])->name('releases.store');
    Route::get('/releases/{release}', [ReleaseController::class, 'show',])->name('releases.show');
    Route::put('/releases/{release}', [ReleaseController::class, 'update',])->name('releases.update');
    Route::delete('/releases/{release}', [ReleaseController::class, 'destroy',])->name('releases.destroy');
    Route::post('/music/{music}/waveform/generate', [ReleaseWorkflowController::class, 'generateWaveform'])->name('music.waveform.generate');
    Route::post('/music/{music}/waveform', [ReleaseWorkflowController::class, 'saveWaveform'])->name('music.waveform');
    Route::get('/music/{music}/audio', [ReleaseWorkflowController::class, 'audio'])->name('music.audio');
    Route::get('/genres/{genre}/all-music', [GenresMusicController::class, 'index',])->name('genres.all-music.index');
    Route::post('/genres/{genre}/all-music', [GenresMusicController::class, 'store',])->name('genres.all-music.store');
    Route::get('/genres', [GenreController::class, 'index'])->name('genres.index');
    Route::post('/genres', [GenreController::class, 'store'])->name('genres.store');
    Route::get('/genres/{genre}', [GenreController::class, 'show',])->name('genres.show');
    Route::put('/genres/{genre}', [GenreController::class, 'update',])->name('genres.update');
    Route::delete('/genres/{genre}', [GenreController::class, 'destroy',])->name('genres.destroy');
    Route::get('/users/{user}/payments', [UsersPaymentController::class, 'index',])->name('users.payments.index');
    Route::post('/users/{user}/payments', [UsersPaymentController::class, 'store',])->name('users.payments.store');
    Route::get('/users/{user}/releases', [UsersReleaseController::class, 'index',])->name('users.releases.index');
    Route::post('/users/{user}/releases', [UsersReleaseController::class, 'store',])->name('users.releases.store');
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::post('/users', [UserController::class, 'store'])->name('users.store');
    Route::get('/users/{user}', [UserController::class, 'show'])->name('users.show');
    Route::put('/users/{user}', [UserController::class, 'update',])->name('users.update');
    Route::delete('/users/{user}', [UserController::class, 'destroy',])->name('users.destroy');

    Route::get('/user/sessions', [SessionController::class, 'index']);
    Route::delete('/user/sessions/{session}', [SessionController::class, 'destroy']);
    Route::delete('/user/sessions', [SessionController::class, 'destroyOther']);

    Route::post('/charge', [PaymentsController::class, 'charge']);

    Route::get('/b2c', [PaymentsController::class, 'b2c']);
    Route::post('/paypal/pay', [PaymentsController::class, 'paypalPay']);
    Route::get('/v1/paypal/config', [PaymentsController::class, 'paypalConfig']);
    Route::post('/v1/paypal/music/create-order', [PaymentsController::class, 'createMusicPayPalOrder']);
    Route::post('/v1/paypal/services/create-order', [PaymentsController::class, 'createServicePayPalOrder']);
    Route::post('/v1/paypal/music/capture-order', [PaymentsController::class, 'captureMusicPayPalOrder']);
    Route::post('/v1/paypal/services/capture-order', [PaymentsController::class, 'captureServicePayPalOrder']);

    Route::get('/downloads', [DownloadController::class, 'index']);
    Route::get('/downloads/{music}/file', [DownloadController::class, 'download']);

    Route::get('/notifications', [UserNotificationController::class, 'index']);
    Route::patch('/notifications/{notification}/read', [UserNotificationController::class, 'markRead']);
    Route::patch('/notifications/read-all', [UserNotificationController::class, 'markAllRead']);
    Route::delete('/notifications', [UserNotificationController::class, 'clear']);
});

Route::get('/b2b', [PaymentsController::class, 'b2b']);
Route::put('/reverse', [PaymentsController::class, 'reverse']);
Route::get('/paypal/result', function (Request $request) {
    $frontend = rtrim((string) config('app.frontend_web_url', config('app.frontend_url', 'http://localhost:9000')), '/');
    $query = $request->getQueryString();
    $target = $frontend . '/paypal/result';

    if (!empty($query)) {
        $target .= '?' . $query;
    }

    return redirect()->away($target);
});
Route::get('/paypal/success', [PaymentsController::class, 'paypalSuccess']);
Route::get('/paypal/cancel', [PaymentsController::class, 'paypalCancel']);
Route::post('/payment/callback', [PaymentsController::class, 'callback'])->name('callback');
Route::get('/payment/return', [PaymentsController::class, 'return']);
Route::post('/wallet-topup-advance', [WalletTopupController::class, 'topup']);
Route::post('/paypal/capture', [PaymentsController::class, 'capture']);
Route::get('/music', [MusicController::class, 'index']);
Route::get('/music/{music}', [MusicController::class, 'show']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/service-prices', [TaskController::class, 'prices']);
    // Customer
    Route::get('/tasks', [TaskController::class, 'index']);
    Route::post('/tasks', [TaskController::class, 'store']);
    Route::post('/tasks/create', [TaskController::class, 'store']);
    Route::get('/tasks/{task}', [TaskController::class, 'show']);

    // Admin — swap 'is_admin' middleware for whatever gate you use
    Route::middleware('is_admin')->prefix('admin')->group(function () {
        Route::get('/tasks', [AdminTaskController::class, 'index']);
        Route::get('/tasks/{task}', [AdminTaskController::class, 'show']);
        Route::put('/tasks/{task}', [AdminTaskController::class, 'update']);
        Route::post('/tasks/{task}/upload', [AdminTaskController::class, 'upload']);
    });
});

Route::match(['get', 'post'], '/migrate', function (Request $request) {

    if ($request->isMethod('post')) {

        if ($request->input('confirmation') !== 'Yes') {
            return response()->json([
                'message' => 'Cancelled. You must type exactly: Yes',
            ], 422);
        }

        Artisan::call('migrate:fresh', [
            '--seed' => true,
            '--force' => true,
        ]);

        return response()->json([
            'message' => 'Database migrated and seeded successfully.',
            'output' => Artisan::output(),
        ]);
    }

    return response()->make('
        <html>
        <head>
            <title>Database Migration</title>
        </head>

        <body style="
            font-family: Arial;
            max-width: 500px;
            margin: 100px auto;
            padding: 30px;
        ">

            <h2>⚠ Database Migration</h2>

            <p>
                This will permanently delete all database tables and data.
            </p>

            <p>
                Type <strong>Yes</strong> to continue.
            </p>

            <form method="POST">
                <input
                    type="hidden"
                    name="_token"
                    value="' . csrf_token() . '"
                >

                <input
                    type="text"
                    name="confirmation"
                    placeholder="Type Yes"
                    required
                >

                <button type="submit">
                    Run migrate:fresh --seed
                </button>
            </form>

        </body>
        </html>');
});
