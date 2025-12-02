<?php

namespace App\Filament\Resources\Purchases\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class PurchasesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('reference')
                    ->label('№')
                    ->searchable()
                    ->copyable(),
                TextColumn::make('supplier.name')
                    ->label('Поставщик')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('purchased_at')
                    ->label('Дата')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
                TextColumn::make('total')
                    ->label('Сумма')
                    ->money('TJS')
                    ->sortable(),
                TextColumn::make('paid_amount')
                    ->label('Оплачено')
                    ->money('TJS')
                    ->sortable(),
                TextColumn::make('balance')
                    ->label('Долг')
                    ->money('TJS')
                    ->sortable()
                    ->color('danger'),
                TextColumn::make('payment_status')
                    ->label('Статус')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'paid' => 'success',
                        'partial' => 'warning',
                        default => 'danger',
                    }),
                TextColumn::make('payment_method')
                    ->label('Оплата')
                    ->formatStateUsing(fn (?string $state) => match ($state) {
                        'cash' => 'Наличные',
                        'card' => 'Карта',
                        'bank' => 'Банк',
                        'credit' => 'В долг',
                        default => '—',
                    }),
                IconColumn::make('is_credit')
                    ->label('Есть долг')
                    ->boolean(),
            ])
            ->filters([
                SelectFilter::make('supplier_id')
                    ->label('Поставщик')
                    ->relationship('supplier', 'name')
                    ->searchable(),
                SelectFilter::make('payment_status')
                    ->label('Статус')
                    ->options([
                        'paid' => 'Оплачено',
                        'partial' => 'Частично',
                        'unpaid' => 'Не оплачено',
                    ]),
                TernaryFilter::make('is_credit')
                    ->label('Долг'),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
