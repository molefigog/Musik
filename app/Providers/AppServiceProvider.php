<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Music;
use App\Models\Payment;
use App\Models\Task;
use App\Observers\MusicObserver;
use App\Observers\PaymentObserver;
use App\Observers\TaskObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Music::observe(MusicObserver::class);
        Payment::observe(PaymentObserver::class);
        Task::observe(TaskObserver::class);
    }
}
