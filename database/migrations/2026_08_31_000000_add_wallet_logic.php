<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            // who receives the money for this payment (the item's owner)
            $table->foreignId('seller_id')->nullable()->after('user_id')
                ->constrained('users')->nullOnDelete();

            // idempotency guard - null until WalletService::creditSeller() has run
            $table->timestamp('credited_at')->nullable()->after('status');
        });

        Schema::create('wallet_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('payment_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type'); // sale_credit | sale_reversal | top_up | cash_out | wallet_spend
            $table->decimal('amount', 12, 2); // negative for debits
            $table->string('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wallet_transactions');

        Schema::table('payments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('seller_id');
            $table->dropColumn('credited_at');
        });
    }
};
