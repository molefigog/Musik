<?php

namespace App\Filament\Widgets;

use App\Models\Release;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ReleasesOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 2;
    protected function getStats(): array
    {
        return [
            Stat::make('Total Releases', Release::count())
                ->descriptionIcon('heroicon-m-musical-note')
                ->color('primary'),

            Stat::make('Published', Release::where('published', true)->count())
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make('Unpublished', Release::where('published', false)->count())
                ->descriptionIcon('heroicon-m-eye-slash')
                ->color('gray'),
        ];
    }
}
