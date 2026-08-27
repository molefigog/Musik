<?php

namespace Database\Seeders;

use App\Models\ServicePrice;
use Illuminate\Database\Seeder;

class ServicePriceSeeder extends Seeder
{
    public function run(): void
    {
        ServicePrice::updateOrCreate(
            ['service_type' => 'beat'],
            ['amount' => 1]
        );

        ServicePrice::updateOrCreate(
            ['service_type' => 'recording'],
            ['amount' => 2]
        );

        ServicePrice::updateOrCreate(
            ['service_type' => 'artwork'],
            ['amount' => 3]
        );
    }
}
