<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class SyncToRemote extends Command
{
    protected $signature = 'sync:remote';
    protected $description = 'Push local data to remote site';

    public function handle()
    {
        $tables = ['products', 'transactions', 'product_adjustments', 'receipt_sequences', 'qoutes', 'users', 'companies', 'categories', 'daily_stocks'];

        foreach ($tables as $table) {
            $data = DB::table($table)->get()->toArray();

            $response = Http::post('https://music.gw-ent.co.za/api/sync-table/' . $table, [
                'rows' => json_decode(json_encode($data), true),
            ]);

            $this->info("Synced {$table}: " . $response->body());
        }
    }
}
