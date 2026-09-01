<?php

namespace App\Filament\UserResources\Pages;

use App\Filament\UserResources\UserPaymentResource;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Widgets\WalletStatsOverview;

class UserPaymentListPage extends ListRecords
{
    protected static string $resource = UserPaymentResource::class;

    protected function getHeaderWidgets(): array
    {
        return [
            WalletStatsOverview::class,
        ];
    }
}