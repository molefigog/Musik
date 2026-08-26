<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();

            $table->decimal('amount');

            $table->string('status');

            $table->string('txn_id');

            $table->string('msisdn');

            $table->string('conversation_id');

            $table->string('type');

            $table->json('raw_response')->nullable();

            $table->string('statusCode');

            $table->text('description');

            $table->timestamp('created_at')->nullable();

            $table->timestamp('updated_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('payments');
    }
};
