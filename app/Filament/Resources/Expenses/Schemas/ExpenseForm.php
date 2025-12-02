<?php

namespace App\Filament\Resources\Expenses\Schemas;

use App\Models\Product;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class ExpenseForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('category')
                    ->label('Категория расхода')
                    ->placeholder('Например: Аренда офиса')
                    ->required(),
                Select::make('type')
                    ->label('Тип расхода')
                    ->options([
                        'delivery' => 'Доставка товаров',
                        'rent' => 'Аренда',
                        'salary' => 'Зарплата',
                        'utilities' => 'Коммунальные услуги',
                        'other' => 'Другое',
                    ])
                    ->native(false)
                    ->default('other'),
                Select::make('currency')
                    ->label('Валюта')
                    ->options([
                        'TJS' => 'Сомони (TJS)',
                        'USD' => 'Доллар (USD)',
                    ])
                    ->default('TJS')
                    ->live(),
                TextInput::make('amount')
                    ->label('Сумма (TJS)')
                    ->required()
                    ->numeric()
                    ->prefix(fn (callable $get) => $get('currency') === 'USD' ? 'USD' : 'SM'),
                DatePicker::make('spent_at')
                    ->label('Дата расхода')
                    ->required()
                    ->default(now()),
                Select::make('payment_method')
                    ->label('Способ оплаты')
                    ->options([
                        'cash' => 'Наличные',
                        'card' => 'Карта/терминал',
                        'bank' => 'Банк',
                    ])
                    ->searchable()
                    ->required()
                    ->native(false),
                TextInput::make('reference')
                    ->label('Номер документа')
                    ->placeholder('Например: счёт №123'),
                Select::make('delivery_products')
                    ->label('Товары доставки')
                    ->multiple()
                    ->searchable()
                    ->preload()
                    ->options(fn () => Product::query()->orderBy('name')->pluck('name', 'id'))
                    ->visible(fn (callable $get) => $get('type') === 'delivery')
                    ->helperText('Укажите товары, к доставке которых относится расход.'),
                Textarea::make('comment')
                    ->label('Комментарий')
                    ->placeholder('Дополнительные сведения')
                    ->columnSpanFull(),
            ]);
    }
}
