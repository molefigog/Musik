<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\DailyStock;
use Carbon\Carbon;

class SetClosingStock extends Command
{
    protected $signature = 'stock:set-closing';
    protected $description = 'Set closing stock based on opening stock - sales';


    public function handle(): int
    {
        $today = Carbon::today()->toDateString();
        $yesterday = Carbon::yesterday()->toDateString();

        $transactions = Transaction::whereDate('created_at', $today)->get();

        if ($transactions->isEmpty()) {
            $this->warn('⚠️ No transactions found for today.');
            return Command::SUCCESS;
        }

        $sales = [];

        foreach ($transactions as $transaction) {
            foreach ($transaction->items as $item) {
                if (!isset($item['id']) || !isset($item['quantity'])) {
                    $this->error("❌ Skipping invalid item in transaction #{$transaction->id}: " . json_encode($item));
                    continue;
                }

                $productId = $item['id'];
                $qty = $item['quantity'];
                $sales[$productId] = ($sales[$productId] ?? 0) + $qty;
            }
        }

        if (empty($sales)) {
            $this->error('❌ No valid sales data found in transactions.');
            return Command::FAILURE;
        }

        foreach ($sales as $productId => $soldToday) {
            $previous = DailyStock::where('product_id', $productId)
                ->whereDate('date', $yesterday)
                ->first();

            $opening = $previous?->closing_stock ?? 0;
            $closing = max(0, $opening - $soldToday);

            DailyStock::updateOrCreate(
                ['product_id' => $productId, 'date' => $today],
                ['opening_stock' => $opening, 'closing_stock' => $closing]
            );

            $this->info("✅ Product #$productId | Opening: $opening | Sold: $soldToday | Closing: $closing");
        }

        return Command::SUCCESS;
    }
}
