<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Product;
use App\Models\DailyStock;
use Carbon\Carbon;

class SetOpeningStock extends Command
{
    protected $signature = 'stock:set-opening';
    protected $description = 'Set opening stock from previous closing stock, reset closing stock, and log daily stock';

    public function handle(): int
    {
        $today = Carbon::today()->toDateString();

        $products = Product::all();

        foreach ($products as $product) {
            $openingStock = $product->closing_stock ?? $product->stock;

            // Save daily stock record
            DailyStock::create([
                'product_id'     => $product->id,
                'date'           => $today,
                'opening_stock'  => $openingStock,
                'closing_stock'  => $product->closing_stock, // might be null if not yet recorded
            ]);

            // Update product record
            $product->opening_stock = $openingStock;
            $product->closing_stock = null;
            $product->closing_stock_updated_at = null;
            $product->save();
        }

        $this->info('Opening stock recorded and closing stock reset for all products.');
        return Command::SUCCESS;
    }
}
