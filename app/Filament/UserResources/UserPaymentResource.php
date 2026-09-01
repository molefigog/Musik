<?php

namespace App\Filament\UserResources;

use App\Models\Payment;
use App\Filament\UserResources\Pages\UserPaymentListPage;
use App\Services\WalletService;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

class UserPaymentResource extends Resource
{
    protected static ?string $model = Payment::class;
    protected static ?string $navigationLabel = 'Payments';
    protected static string|BackedEnum|null $navigationIcon = Heroicon::Wallet;
    protected static ?int $navigationSort = 3;

    public static function getGloballySearchableAttributes(): array
    {
        return ['title'];
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            //
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->sortable(),
                TextColumn::make('amount')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->colors([
                        'success' => 'completed',
                        'warning' => 'pending',
                        'danger' => 'failed',
                    ]),
                TextColumn::make('type')
                    ->searchable(),
                TextColumn::make('seller_id')
                    ->label('Direction')
                    ->getStateUsing(fn(Payment $record) => $record->seller_id === Auth::id() ? 'Sale' : 'Purchase')
                    ->badge()
                    ->color(fn(string $state) => $state === 'Sale' ? 'success' : 'gray'),
                TextColumn::make('credited_at')
                    ->label('Credited')
                    ->dateTime()
                    ->placeholder('—')
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->headerActions([
                static::topUpAction(),
                static::cashOutAction(),
                static::summaryPdfAction(),
            ])
            ->filters([
                //
            ])
            ->actions([
                //
            ])
            ->bulkActions([
                //
            ]);
    }

    protected static function topUpAction(): Action
    {
        return Action::make('topUp')
            ->label('Top Up Wallet')
            ->icon(Heroicon::Wallet)
            ->color('success')
            ->schema([
                TextInput::make('amount')
                    ->numeric()
                    ->required()
                    ->minValue(1)
                    ->prefix('M')
                    ->helperText('Moves funds from your balance into your wallet.'),
            ])
            ->action(function (array $data, WalletService $wallet) {
                try {
                    $wallet->topUp(Auth::user(), (float) $data['amount']);

                    Notification::make()
                        ->title('Wallet topped up')
                        ->success()
                        ->send();
                } catch (\RuntimeException $e) {
                    Notification::make()
                        ->title('Top up failed')
                        ->body($e->getMessage())
                        ->danger()
                        ->send();
                }
            });
    }

    protected static function cashOutAction(): Action
    {
        return Action::make('cashOut')
            ->label('Cash Out')
            ->icon(Heroicon::ArrowUturnLeft)
            ->color('gray')
            ->schema([
                TextInput::make('amount')
                    ->numeric()
                    ->required()
                    ->minValue(1)
                    ->prefix('M')
                    ->helperText('Moves funds from your wallet back to your balance.'),
            ])
            ->action(function (array $data, WalletService $wallet) {
                try {
                    $wallet->cashOut(Auth::user(), (float) $data['amount']);

                    Notification::make()
                        ->title('Wallet cashed out')
                        ->success()
                        ->send();
                } catch (\RuntimeException $e) {
                    Notification::make()
                        ->title('Cash out failed')
                        ->body($e->getMessage())
                        ->danger()
                        ->send();
                }
            });
    }

    protected static function summaryPdfAction(): Action
    {
        return Action::make('summaryPdf')
            ->label('Preview Summary')
            ->icon(Heroicon::DocumentArrowDown)
            ->color('gray')
            ->schema([
                DatePicker::make('from')->required()->default(now()->subDays(30)),
                DatePicker::make('to')->required()->default(now()),
                Select::make('type')
                    ->options(['purchases' => 'Purchases', 'sales' => 'Sales'])
                    ->default('purchases')
                    ->required(),
            ])

            ->url(fn(array $data) => route('wallet.summary.preview', $data))
            ->openUrlInNewTab();
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getEloquentQuery()->count();
    }
    public static function getPages(): array
    {
        return [
            'index' => UserPaymentListPage::route('/'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where(function (Builder $query) {
                $query->where('user_id', Auth::id())
                    ->orWhere('seller_id', Auth::id());
            });
    }
}