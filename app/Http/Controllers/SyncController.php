<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use App\Models\Transaction;

class SyncController extends Controller
{
    public function syncToRemote()
    {
        $remoteApiUrl = 'https://music.gw-ent.co.za/api/sync-sales';
        $syncedIds = DB::table('sync_logs')->where('table', 'transactions')->pluck('record_id');
        $transactions = Transaction::whereNotIn('id', $syncedIds)->get();

        if ($transactions->isEmpty()) {
            return response()->json(['status' => 'empty', 'message' => 'No new transactions to sync.'], 200);
        }

        $response = Http::post($remoteApiUrl, [
            'sales' => $transactions->toArray(),
        ]);

        if ($response->successful()) {
            foreach ($transactions as $t) {
                DB::table('sync_logs')->insertOrIgnore([
                    'table' => 'transactions',
                    'record_id' => $t->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        return $response->json();
    }
}
