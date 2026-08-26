<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('filament_theme_settings')) {
            return;
        }

        Schema::create('filament_theme_settings', function (Blueprint $table): void {
            $table->id();
            $table->string('accent_color', 20)->default('#cba24c');
            $table->string('theme_mode', 10)->default('auto');
            $table->string('preset', 100)->default('corporate');
            $table->string('app_name', 255)->nullable();
            $table->string('logo_url', 2048)->nullable();
            $table->string('dark_logo_url', 2048)->nullable();
            $table->unsignedSmallInteger('logo_height')->default(40);
            $table->json('tokens')->nullable();
            $table->boolean('show_left_sidebar')->default(true);
            $table->boolean('compact_sidebar')->default(false);
            $table->string('navigation_layout', 32)->default('sidebar');
            $table->boolean('show_mode_switcher')->default(true);
            $table->boolean('show_apps_dropdown')->default(true);
            $table->string('font_family', 32)->default('filament_default');
            $table->unsignedTinyInteger('base_font_size')->default(14);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('filament_theme_settings');
    }
};
