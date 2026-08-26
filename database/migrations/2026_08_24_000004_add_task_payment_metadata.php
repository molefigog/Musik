<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table): void {
            $table->string('service_type')->nullable()->after('service_id');
            $table->string('title')->nullable()->after('service_type');
        });

        Schema::table('tasks', function (Blueprint $table): void {
            $table->foreignId('payment_id')->nullable()->after('user_id')->constrained('payments')->nullOnDelete();
            $table->unique('payment_id');
        });
    }

    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table): void {
            $table->dropForeign(['payment_id']);
            $table->dropUnique(['payment_id']);
            $table->dropColumn('payment_id');
        });

        Schema::table('payments', function (Blueprint $table): void {
            $table->dropColumn(['service_type', 'title']);
        });
    }
};
