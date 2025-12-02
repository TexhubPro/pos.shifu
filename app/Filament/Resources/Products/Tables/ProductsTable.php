<?php

namespace App\Filament\Resources\Products\Tables;

use App\Models\Product;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Товар')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('brand.name')
                    ->label('Бренд')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('category.name')
                    ->label('Категория')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('sku')
                    ->label('SKU')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('sale_price')
                    ->label('Цена')
                    ->money('TJS')
                    ->sortable(),
                TextColumn::make('wholesale_price')
                    ->label('Опт')
                    ->money('TJS')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('stock')
                    ->label('Остаток')
                    ->numeric(2)
                    ->badge()
                    ->color(fn (Product $record) => $record->stock <= $record->low_stock_threshold ? 'danger' : 'success')
                    ->sortable(),
                TextColumn::make('cost_price_usd')
                    ->label('Себестоимость (USD)')
                    ->formatStateUsing(fn ($state) => number_format($state, 2))
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('is_active')
                    ->label('Активен')
                    ->boolean(),
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
                SelectFilter::make('brand_id')
                    ->label('Бренд')
                    ->relationship('brand', 'name')
                    ->searchable(),
                SelectFilter::make('category_id')
                    ->label('Категория')
                    ->relationship('category', 'name')
                    ->searchable(),
                TernaryFilter::make('is_active')
                    ->label('Активность'),
                Filter::make('low_stock')
                    ->label('Мало на складе')
                    ->query(fn ($query) => $query->whereColumn('stock', '<=', 'low_stock_threshold')),
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
