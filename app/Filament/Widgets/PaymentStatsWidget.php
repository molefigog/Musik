<?php

namespace App\Filament\Widgets;

use App\Models\Payment;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PaymentStatsWidget extends BaseWidget
{
    protected static ?int $sort = 1;
    protected function getStats(): array
    {
        $successfulToday = Payment::query()
            ->where('status', 'success')
            ->whereDate('created_at', today())
            ->sum('amount');

        $totalRevenue = Payment::query()
            ->where('status', 'success')
            ->sum('amount');

        $pendingCount = Payment::query()
            ->where('status', 'pending')
            ->count();

        $totalWallets = User::sum('wallet');
        $totalBalances = User::sum('balance');

        return [
            Stat::make('Total Revenue', 'R ' . number_format($totalRevenue, 2))
                ->description('All successful payments')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),

            Stat::make('Revenue Today', 'R ' . number_format($successfulToday, 2))
                ->description('Successful payments today')
                ->descriptionIcon('heroicon-m-calendar')
                ->color('primary'),

            Stat::make('Pending Payments', $pendingCount)
                ->description('Awaiting confirmation')
                ->descriptionIcon('heroicon-m-clock')
                ->color($pendingCount > 0 ? 'warning' : 'gray'),

            Stat::make('User Wallets + Balances', 'R ' . number_format($totalWallets + $totalBalances, 2))
                ->description('Wallet: R ' . number_format($totalWallets, 2) . ' · Balance: R ' . number_format($totalBalances, 2))
                ->descriptionIcon('heroicon-m-wallet')
                ->color('info'),
        ];
    }
}
