<?php

namespace App\Filament\Resources\Tasks\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TasksTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->sortable(),

                TextColumn::make('user.name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('service_type')
                    ->badge()
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'beat' => 'Beat',
                        'recording' => 'Recording',
                        'artwork' => 'Artwork',
                        default => ucfirst($state),
                    }),

                TextColumn::make('title')
                    ->searchable()
                    ->limit(40),

                TextColumn::make('amount')
                    ->money('ZAR')
                    ->sortable(),

                IconColumn::make('is_paid')
                    ->label('Paid')
                    ->boolean(),

                IconColumn::make('status')
                    ->label('Completed')
                    ->boolean(),

                IconColumn::make('file_path')
                    ->label('Has File')
                    ->boolean(fn($state): bool => filled($state)),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
