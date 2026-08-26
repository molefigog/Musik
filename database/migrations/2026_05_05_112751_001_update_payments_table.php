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
        Schema::table('payments', function (Blueprint $table) {
            $table
                ->bigInteger('music_id')
                ->unsigned()
                ->after('user_id');

            $table
                ->string('statusCode')
                ->nullable()
                ->change();

            $table
                ->text('description')
                ->nullable()
                ->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn('music_id');

            $table->string('statusCode')->change();

            $table->text('description')->change();
        });
    }
};
