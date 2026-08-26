<?php

namespace App\Filament\Resources\Users\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Actions\Action;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\RepeatableEntry\TableColumn;


class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('email')
                    ->label('Email address')
                    ->searchable(),

                TextColumn::make('wallet')
                    ->money('ZAR'),
                TextColumn::make('balance')
                    ->money('ZAR'),
                TextColumn::make('tel')
                    ->searchable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),


                Action::make('payments')
                    ->schema([
                        RepeatableEntry::make('payments')
                            ->state(fn($record) => $record->payments()
                                ->latest('id')
                                ->limit(10)
                                ->get())
                            ->table([
                                TableColumn::make('Amount'),
                                TableColumn::make('Status'),
                                TableColumn::make('TXN ID'),
                                TableColumn::make('Method'),
                            ])
                            ->schema([
                                TextEntry::make('amount')
                                    ->money('ZAR'),

                                TextEntry::make('status')
                                    ->badge()
                                    ->icon(fn(string $state): string => match ($state) {
                                        'pending' => 'heroicon-m-clock',
                                        'failed' => 'heroicon-m-x-circle',
                                        'completed' => 'heroicon-m-check-circle',
                                        default => 'heroicon-m-question-mark-circle',
                                    })
                                    ->color(fn(string $state): string => match ($state) {
                                        'pending' => 'warning',
                                        'failed' => 'danger',
                                        'completed' => 'success',
                                        default => 'gray',
                                    }),
                                TextEntry::make('txn_id'),


                                TextEntry::make('type')
                                    ->label('Method'),
                            ])
                    ])
                    ->modalSubmitAction(false)
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
