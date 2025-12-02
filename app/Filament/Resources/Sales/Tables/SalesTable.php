<?php

namespace App\Filament\Resources\Sales\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class SalesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('reference')
                    ->label('Чек №')
                    ->searchable()
                    ->copyable(),
                TextColumn::make('client.name')
                    ->label('Клиент')
                    ->sortable()
                    ->searchable()
                    ->placeholder('Покупатель'),
                TextColumn::make('sold_at')
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
                TextColumn::make('payment_method')
                    ->label('Оплата')
                    ->formatStateUsing(fn (?string $state) => match ($state) {
                        'cash' => 'Наличные',
                        'card' => 'Карта',
                        'mixed' => 'Смешанная',
                        'debt' => 'В долг',
                        default => '—',
                    }),
                TextColumn::make('status')
                    ->label('Статус')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'completed' => 'success',
                        'pending' => 'warning',
                        'refunded' => 'danger',
                        default => 'gray',
                    }),
                IconColumn::make('on_credit')
                    ->label('В долг')
                    ->boolean(),
                TextColumn::make('due_at')
                    ->label('Срок оплаты')
                    ->date()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Статус')
                    ->options([
                        'completed' => 'Завершено',
                        'pending' => 'В процессе',
                        'draft' => 'Черновик',
                        'refunded' => 'Возврат',
                    ]),
                SelectFilter::make('payment_method')
                    ->label('Способ оплаты')
                    ->options([
                        'cash' => 'Наличные',
                        'card' => 'Карта',
                        'mixed' => 'Смешанная',
                        'debt' => 'В долг',
                    ]),
                TernaryFilter::make('on_credit')
                    ->label('Только долги'),
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
