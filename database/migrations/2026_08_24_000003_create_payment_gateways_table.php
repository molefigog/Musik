<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_gateways', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->boolean('enabled')->default(true);
            $table->timestamps();
        });

        DB::table('payment_gateways')->insert([
            ['name' => 'Cpay Card', 'slug' => 'card', 'enabled' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Cpay Mobile', 'slug' => 'mobile', 'enabled' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'M-Pesa', 'slug' => 'mpesa', 'enabled' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'PayPal', 'slug' => 'paypal', 'enabled' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'PayPal Card', 'slug' => 'paypal_card', 'enabled' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_gateways');
    }
};
