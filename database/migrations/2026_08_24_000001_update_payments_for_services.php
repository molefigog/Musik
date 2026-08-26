<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->unsignedBigInteger('music_id')->nullable()->change();
            $table->unsignedBigInteger('service_id')->nullable()->after('music_id');
            $table->index('service_id');
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropIndex(['service_id']);
            $table->dropColumn('service_id');
            $table->unsignedBigInteger('music_id')->nullable(false)->change();
        });
    }
};
