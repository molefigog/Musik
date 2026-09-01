<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class WalletStatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $user = Auth::user();

        return [
            Stat::make('Balance', number_format($user->balance, 2))
                ->description('Available from sales')
                ->color('success'),

            Stat::make('Wallet', number_format($user->wallet, 2))
                ->description('Usable to buy items')
                ->color('info'),
        ];
    }
}
