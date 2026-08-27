<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;
use App\Filament\Widgets\PaymentStatsWidget;
use App\Filament\Widgets\RevenueChartWidget;
use App\Filament\Widgets\ReleasesOverviewWidget;
use App\Filament\Widgets\LatestPaymentsWidget;
use App\Filament\Widgets\LatestNotificationsWidget;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;

class Dashboard extends BaseDashboard
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected string $view = 'filament.pages.dashboard';
    public function getWidgets(): array
    {
        return [
            PaymentStatsWidget::class,
            RevenueChartWidget::class,
            ReleasesOverviewWidget::class,
            LatestPaymentsWidget::class,
            LatestNotificationsWidget::class,
        ];
    }

    public function getColumns(): array | int
    {
        return [
            'md' => 2,
            'xl' => 3,
        ];
    }
}
