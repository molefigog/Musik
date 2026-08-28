<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Clean up existing duplicates, keeping only the most recently updated row per user.
        $duplicateUserIds = DB::table('fcm_tokens')
            ->select('user_id')
            ->groupBy('user_id')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('user_id');

        foreach ($duplicateUserIds as $userId) {
            $keepId = DB::table('fcm_tokens')
                ->where('user_id', $userId)
                ->orderByDesc('updated_at')
                ->value('id');

            DB::table('fcm_tokens')
                ->where('user_id', $userId)
                ->where('id', '!=', $keepId)
                ->delete();
        }

        // 2. Enforce one token per user going forward.
        Schema::table('fcm_tokens', function (Blueprint $table) {
            $table->unique('user_id');
        });
    }

    public function down(): void
    {
        Schema::table('fcm_tokens', function (Blueprint $table) {
            $table->dropUnique(['user_id']);
        });
    }
};
