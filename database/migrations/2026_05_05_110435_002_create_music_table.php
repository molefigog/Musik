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
        Schema::create('music', function (Blueprint $table) {
            $table->id();

            $table->string('title');

            $table->string('file_src');

            $table->string('size')->nullable();

            $table->decimal('price', 10, 2)->nullable();

            $table->string('duration')->nullable();

            $table->foreignId('release_id')->constrained('releases')->onDelete('cascade');

            $table->foreignId('genre_id')->constrained('genres')->onDelete('cascade');

            $table->boolean('is_sold')->default(false);

            $table->string('extension')->nullable();

            $table->string('file_name')->nullable();

            $table->longText('waveform')->nullable();

            $table->boolean('is_published')->default(false);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('music');
    }
};
