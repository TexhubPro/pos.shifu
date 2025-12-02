<?php

namespace App\Filament\Resources\Expenses\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ExpensesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('category')
                    ->label('Категория')
                    ->searchable(),
                TextColumn::make('type')
                    ->label('Тип')
                    ->badge()
                    ->color(fn (?string $state) => match ($state) {
                        'delivery' => 'info',
                        'rent' => 'warning',
                        'salary' => 'success',
                        'utilities' => 'secondary',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (?string $state) => match ($state) {
                        'delivery' => 'Доставка',
                        'rent' => 'Аренда',
                        'salary' => 'Зарплата',
                        'utilities' => 'Коммунальные',
                        'other' => 'Другое',
                        default => '—',
                    }),
                TextColumn::make('amount')
                    ->label('Сумма')
                    ->money('TJS')
                    ->sortable(),
                TextColumn::make('spent_at')
                    ->label('Дата')
                    ->date('d.m.Y')
                    ->sortable(),
                TextColumn::make('payment_method')
                    ->label('Способ оплаты')
                    ->searchable()
                    ->placeholder('—'),
                TextColumn::make('reference')
                    ->label('Документ')
                    ->searchable()
                    ->placeholder('—'),
                TextColumn::make('delivery_products')
                    ->label('Товары доставки')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->formatStateUsing(function (?array $state) {
                        if (blank($state)) {
                            return '—';
                        }

                        return 'Выбрано: ' . count($state);
                    }),
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
