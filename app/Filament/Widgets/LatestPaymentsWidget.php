<?php

namespace App\Filament\Widgets;

use App\Models\Payment;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LatestPaymentsWidget extends BaseWidget
{
    protected static ?string $heading = 'Latest Payments';
    protected static ?int $sort = 3;
    protected int | string | array $columnSpan = 'full';
    public function table(Table $table): Table
    {
        return $table
            ->query(Payment::query()->latest())
            ->columns([
                Tables\Columns\TextColumn::make('item_name')
                    ->label('Item')
                    ->limit(30),
                Tables\Columns\TextColumn::make('msisdn')
                    ->label('MSISDN'),
                Tables\Columns\TextColumn::make('amount')
                    ->money('ZAR'),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'success' => 'success',
                        'pending' => 'warning',
                        'failed' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('type'),
                Tables\Columns\TextColumn::make('created_at')
                    ->since()
                    ->label('When'),
            ])
            ->paginated(false)
            ->defaultSort('created_at', 'desc')
            ->poll('30s');
    }
}
