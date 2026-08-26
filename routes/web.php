<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\File;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\PaymentsController;
use Illuminate\Http\Request;
use App\Models\Music;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;


Route::get('/paypal/success', [PaymentsController::class, 'paypalSuccess']);
Route::get('/paypal/cancel', [PaymentsController::class, 'paypalCancel']);
Route::get('/transactions/export', [TransactionController::class, 'export']);
Route::get('/send-email', [TransactionController::class, 'exportAndSendEmail']);
// This must be last
// Route::get('/{any}', function () {
//     return File::get(public_path('index.html'));
// })->where('any', '^(?!api).*$');
Route::post('/waveform/{music}', function (Request $request, Music $music) {
    Log::info('Waveform upload started', [
        'music_id' => $music->id,
    ]);
    if (! $request->hasFile('waveform_file')) {
        Log::warning('No waveform file received');
        return back();
    }
    $file = $request->file('waveform_file');
    $path = $file->store('waveforms', 'public');
    $music->update([
        'waveform' => $path,
        'is_published' => $request->has('is_published'),
    ]);
    Log::info('Waveform saved as file', [
        'path' => $path,
    ]);

    return redirect()->route(
        'filament.admin.resources.releases.edit',
        $music->release
    );
})->name('waveform.save');

// Route::get('/', [App\Http\Controllers\ChaperoneController::class, 'checkout'])->name('payment.checkout');
Route::post('/payment', [App\Http\Controllers\ChaperoneController::class, 'checkout'])->name('payment.checkout.post');
Route::get('/{any}', function () {
    return File::get(public_path('index.html'));
})->where('any', '^(?!api).*$');
