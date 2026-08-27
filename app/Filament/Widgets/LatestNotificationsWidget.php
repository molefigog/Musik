<?php

namespace App\Filament\Widgets;

use App\Models\UserNotification;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LatestNotificationsWidget extends BaseWidget
{
    protected static ?string $heading = 'Latest Notifications';
    protected static ?int $sort = 5;
    protected int | string | array $columnSpan = 'full';
    public function table(Table $table): Table
    {
        return $table
            ->query(UserNotification::query()->latest())
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('User'),
                Tables\Columns\TextColumn::make('title')
                    ->limit(30),
                Tables\Columns\TextColumn::make('source')
                    ->badge(),
                Tables\Columns\IconColumn::make('read_at')
                    ->label('Read')
                    ->boolean()
                    ->getStateUsing(fn($record) => filled($record->read_at)),
                Tables\Columns\TextColumn::make('created_at')
                    ->since(),
            ])
            ->paginated(false)
            ->defaultSort('created_at', 'desc')
            ->poll('30s');
    }
}
