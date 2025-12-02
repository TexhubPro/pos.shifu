<?php

namespace App\Filament\Resources\Products\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class StockMovementsRelationManager extends RelationManager
{
    protected static string $relationship = 'stockMovements';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                TextColumn::make('occurred_at')
                    ->label('Дата')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
                TextColumn::make('type')
                    ->label('Тип')
                    ->badge()
                    ->color(fn (string $state) => $state === 'sale' ? 'danger' : ($state === 'purchase' ? 'success' : 'gray')),
                TextColumn::make('quantity')
                    ->label('Количество')
                    ->numeric(2),
                TextColumn::make('stock_after')
                    ->label('Остаток после')
                    ->numeric(2),
                TextColumn::make('referenceable_type')
                    ->label('Документ')
                    ->formatStateUsing(fn ($state, $record) => class_basename($state) . ' #' . ($record->referenceable?->reference ?? $record->referenceable_id)),
                TextColumn::make('comment')
                    ->label('Комментарий')
                    ->limit(30),
            ])
            ->filters([])
            ->headerActions([])
            ->recordActions([])
            ->bulkActions([]);
    }
}
