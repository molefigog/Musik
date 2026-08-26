<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Music;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DownloadController extends Controller
{
    private const COMPLETED_STATUSES = ['completed', 'Completed'];

    public function index(Request $request)
    {
        $userId = $request->user()->id;

        $payments = Payment::query()
            ->where('user_id', $userId)
            ->whereNotNull('music_id')
            ->whereIn('status', self::COMPLETED_STATUSES)
            ->with(['music.release', 'music.genre'])
            ->latest('id')
            ->get()
            ->filter(fn(Payment $payment) => $payment->music && filled($payment->music->file_src))
            ->unique('music_id')
            ->values();

        $downloads = $payments->map(function (Payment $payment) {
            $music = $payment->music;

            return [
                'music_id' => $music->id,
                'payment_id' => $payment->id,
                'title' => $music->title,
                'file_name' => $music->file_name,
                'extension' => $music->extension,
                'size' => $music->size,
                'duration' => $music->duration,
                'release' => $music->release?->title,
                'genre' => $music->genre?->title,
                'paid_amount' => $payment->amount,
                'purchased_at' => optional($payment->created_at)->toDateTimeString(),
            ];
        });

        return response()->json([
            'data' => $downloads,
        ]);
    }

    public function download(Request $request, Music $music)
    {
        $hasPurchased = Payment::query()
            ->where('user_id', $request->user()->id)
            ->where('music_id', $music->id)
            ->whereIn('status', self::COMPLETED_STATUSES)
            ->exists();

        if (! $hasPurchased) {
            return response()->json([
                'message' => 'You have not purchased this file.',
            ], 403);
        }

        if (blank($music->file_src)) {
            return response()->json([
                'message' => 'File is not available for download.',
            ], 404);
        }

        $disk = Storage::disk('public');

        if (! $disk->exists($music->file_src)) {
            return response()->json([
                'message' => 'File not found.',
            ], 404);
        }

        $downloadName = $music->file_name ?: basename($music->file_src);

        if ($music->extension && ! str_ends_with(strtolower($downloadName), '.' . strtolower($music->extension))) {
            $downloadName .= '.' . $music->extension;
        }

        return response()->download($disk->path($music->file_src), $downloadName);
    }
}
