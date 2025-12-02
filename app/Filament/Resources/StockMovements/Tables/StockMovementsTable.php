<?php

namespace App\Filament\Resources\StockMovements\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class StockMovementsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('product.name')
                    ->label('Товар')
                    ->searchable(),
                TextColumn::make('referenceable_type')
                    ->label('Тип документа')
                    ->badge()
                    ->formatStateUsing(fn (?string $state) => match ($state) {
                        'App\\Models\\Purchase' => 'Закупка',
                        'App\\Models\\Sale' => 'Продажа',
                        default => $state,
                    })
                    ->searchable(),
                TextColumn::make('referenceable_id')
                    ->label('ID документа')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('type')
                    ->label('Тип движения')
                    ->badge()
                    ->color(fn (?string $state) => match ($state) {
                        'sale' => 'danger',
                        'purchase' => 'success',
                        'return' => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (?string $state) => match ($state) {
                        'sale' => 'Продажа',
                        'purchase' => 'Поступление',
                        'correction' => 'Коррекция',
                        'adjustment' => 'Корректировка',
                        'return' => 'Возврат',
                        default => $state,
                    })
                    ->searchable(),
                TextColumn::make('quantity')
                    ->label('Количество')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('stock_after')
                    ->label('Остаток после')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('occurred_at')
                    ->label('Дата движения')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
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
