<?php

namespace App\Filament\Resources\Payments\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PaymentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('amount')
                    ->money('ZAR')
                    ->sortable(),
                TextColumn::make('status')
                    ->searchable()->badge()
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
                TextColumn::make('txn_id')
                    ->searchable(),
                TextColumn::make('music_id')
                    ->label('Music ID')
                    ->sortable(),
                TextColumn::make('service_id')
                    ->label('Service / Task ID')
                    ->sortable(),
                // TextColumn::make('msisdn')
                //     ->searchable(),

                TextColumn::make('type')
                    ->searchable()->label('Payment Method'),


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
            ]);
        // ->recordActions([
        //     EditAction::make(),
        // ])
        // ->toolbarActions([
        //     BulkActionGroup::make([
        //         DeleteBulkAction::make(),
        //     ]),
        // ]);
    }
}
