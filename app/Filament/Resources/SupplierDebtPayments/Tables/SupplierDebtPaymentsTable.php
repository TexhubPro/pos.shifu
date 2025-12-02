<?php

namespace App\Filament\Resources\SupplierDebtPayments\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SupplierDebtPaymentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('supplier.name')
                    ->label('Поставщик')
                    ->searchable(),
                TextColumn::make('purchase.reference')
                    ->label('Закупка')
                    ->placeholder('—')
                    ->searchable(),
                TextColumn::make('user.name')
                    ->label('Ответственный')
                    ->placeholder('—')
                    ->searchable(),
                TextColumn::make('paid_at')
                    ->label('Дата оплаты')
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
                        default => $state,
                    })
                    ->searchable(),
                TextColumn::make('created_at')
                    ->label('Создано')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label('Обновлено')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make()
                    ->label('Редактировать'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label('Удалить выбранные'),
                ]),
            ]);
    }
}
