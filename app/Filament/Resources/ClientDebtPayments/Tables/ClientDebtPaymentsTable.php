<?php

namespace App\Filament\Resources\ClientDebtPayments\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ClientDebtPaymentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('client.name')
                    ->label('Клиент')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('sale.reference')
                    ->label('Продажа')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('paid_at')
                    ->label('Дата')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
                TextColumn::make('amount')
                    ->label('Сумма')
                    ->money('TJS')
                    ->sortable(),
                TextColumn::make('method')
                    ->label('Способ')
                    ->badge()
                    ->formatStateUsing(fn (?string $state) => match ($state) {
                        'cash' => 'Наличные',
                        'card' => 'Карта',
                        'bank' => 'Банк',
                        default => '—',
                    }),
                TextColumn::make('user.name')
                    ->label('Принял')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('method')
                    ->label('Способ оплаты')
                    ->options([
                        'cash' => 'Наличные',
                        'card' => 'Карта',
                        'bank' => 'Банк',
                    ]),
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
