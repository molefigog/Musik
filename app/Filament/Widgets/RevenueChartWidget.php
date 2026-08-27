<?php

namespace App\Filament\Widgets;

use App\Models\Payment;
use Filament\Widgets\ChartWidget;

class RevenueChartWidget extends ChartWidget
{
    protected ?string $heading = 'Revenue (Last 14 Days)';

    protected static ?int $sort = 4;
    protected int | string | array $columnSpan = 'full';
    protected function getData(): array
    {
        $days = collect(range(13, 0))->map(fn($i) => now()->subDays($i)->toDateString());

        $amounts = $days->map(function ($day) {
            return Payment::query()
                ->where('status', 'success')
                ->whereDate('created_at', $day)
                ->sum('amount');
        });

        return [
            'datasets' => [
                [
                    'label' => 'Revenue',
                    'data' => $amounts->toArray(),
                    'borderColor' => '#fa2d48',
                    'backgroundColor' => 'rgba(250, 45, 72, 0.1)',
                    'fill' => true,
                ],
            ],
            'labels' => $days->map(fn($d) => \Carbon\Carbon::parse($d)->format('d M'))->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
