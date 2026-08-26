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
        Schema::table('users', function (Blueprint $table) {
            $table
                ->decimal('wallet')
                ->default(0)
                ->after('profile_photo_path');

            $table
                ->decimal('balance')
                ->default(0)
                ->after('wallet');

            $table
                ->string('tel')
                ->nullable()
                ->after('balance');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('wallet');
            $table->dropColumn('balance');
            $table->dropColumn('tel');
        });
    }
};
