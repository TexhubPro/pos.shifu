<?php

namespace App\Filament\Resources\CurrencyRates\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class CurrencyRatesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('currency_code')
                    ->label('Валюта')
                    ->searchable(),
                TextColumn::make('rate')
                    ->label('Курс (TJS)')
                    ->numeric(4)
                    ->sortable(),
                TextColumn::make('effective_at')
                    ->label('Действует с')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label('Текущий')
                    ->boolean(),
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
                TernaryFilter::make('is_active')
                    ->label('Только активные'),
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
