<?php

namespace App\Filament\Resources\SupplierDebtPayments\Schemas;

use App\Models\Purchase;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class SupplierDebtPaymentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('supplier_id')
                    ->label('Поставщик')
                    ->relationship('supplier', 'name')
                    ->required()
                    ->searchable()
                    ->preload(),
                Select::make('purchase_id')
                    ->label('Закупка')
                    ->placeholder('Без привязки')
                    ->options(fn () => Purchase::query()
                        ->orderByDesc('purchased_at')
                        ->limit(30)
                        ->pluck('reference', 'id'))
                    ->searchable()
                    ->getSearchResultsUsing(fn (string $search) => Purchase::query()
                        ->where('reference', 'like', "%{$search}%")
                        ->orderByDesc('purchased_at')
                        ->limit(30)
                        ->pluck('reference', 'id'))
                    ->getOptionLabelUsing(fn ($value) => Purchase::query()->find($value)?->reference)
                    ->nullable(),
                Select::make('user_id')
                    ->label('Ответственный')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->default(fn () => auth()->id())
                    ->nullable(),
                DateTimePicker::make('paid_at')
                    ->label('Дата оплаты')
                    ->seconds(false)
                    ->required(),
                Select::make('currency')
                    ->label('Валюта')
                    ->options([
                        'TJS' => 'Сомони (TJS)',
                        'USD' => 'Доллар (USD)',
                    ])
                    ->default('TJS')
                    ->live()
                    ->required(),
                TextInput::make('amount')
                    ->label('Сумма')
                    ->required()
                    ->numeric()
                    ->default(0)
                    ->prefix(fn (callable $get) => $get('currency') === 'USD' ? 'USD' : 'SM'),
                Select::make('method')
                    ->label('Способ оплаты')
                    ->options([
                        'cash' => 'Наличные',
                        'card' => 'Карта',
                        'bank' => 'Банк',
                    ])
                    ->required()
                    ->default('cash'),
                Textarea::make('notes')
                    ->label('Комментарий')
                    ->columnSpanFull(),
            ]);
    }
}
