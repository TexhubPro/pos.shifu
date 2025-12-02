<?php

namespace App\Filament\Resources\Purchases\Schemas;

use App\Models\Product;
use App\Services\CurrencyService;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class PurchaseForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Hidden::make('user_id')
                    ->default(fn() => auth()->id()),
                Section::make('Детали закупки')
                    ->columns(2)
                    ->schema([
                        Select::make('supplier_id')
                            ->label('Поставщик')
                            ->relationship('supplier', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        DateTimePicker::make('purchased_at')
                            ->label('Дата закупки')
                            ->required()
                            ->seconds(false),
                        Select::make('purchase_currency')
                            ->label('Валюта закупки')
                            ->options([
                                'TJS' => 'Сомони (TJS)',
                                'USD' => 'Доллар (USD)',
                            ])
                            ->default('TJS')
                            ->live()
                            ->afterStateUpdated(function (Set $set, ?string $state): void {
                                $set('exchange_rate', $state === 'USD' ? app(CurrencyService::class)->getActiveRate() : 1);
                            })
                            ->required(),
                        TextInput::make('exchange_rate')
                            ->label('Курс USD → TJS')
                            ->numeric()
                            ->default(1)
                            ->helperText('Автоматически подставляется при выборе доллара.')
                            ->required(),
                        TextInput::make('shipping_cost')
                            ->label('Доставка (TJS)')
                            ->numeric()
                            ->default(0)
                            ->prefix('TJS'),
                        TextInput::make('discount_amount')
                            ->label('Скидка (TJS)')
                            ->numeric()
                            ->default(0)
                            ->prefix('TJS'),
                        Select::make('payment_method')
                            ->label('Способ оплаты')
                            ->options([
                                'cash' => 'Наличные',
                                'card' => 'Карта/терминал',
                                'bank' => 'Банк',
                                'credit' => 'В долг',
                            ])
                            ->native(false),
                        TextInput::make('paid_amount')
                            ->label('Оплачено (TJS)')
                            ->numeric()
                            ->default(0)
                            ->prefix('TJS'),
                        Toggle::make('is_credit')
                            ->label('Закупка в долг')
                            ->inline(false)
                            ->reactive(),
                        DatePicker::make('due_at')
                            ->label('Срок оплаты')
                            ->visible(fn(callable $get) => (bool) $get('is_credit')),
                        Textarea::make('notes')
                            ->label('Комментарий')
                            ->columnSpanFull()
                            ->rows(3),
                    ]),
                Section::make('Позиции')
                    ->schema([
                        Repeater::make('items')
                            ->label('Товары')
                            ->columns(4)
                            ->minItems(1)
                            ->defaultItems(1)
                            ->addActionLabel('Добавить товар')
                            ->schema([
                                Select::make('product_id')
                                    ->label('Товар')
                                    ->options(fn() => Product::query()->orderBy('name')->pluck('name', 'id'))
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->columnSpan(2),
                                TextInput::make('quantity')
                                    ->label('Количество')
                                    ->numeric()
                                    ->default(1)
                                    ->minValue(0.01)
                                    ->required(),
                                TextInput::make('unit_price')
                                    ->label('Цена')
                                    ->numeric()
                                    ->required()
                                    ->prefix(fn(Get $get) => $get('../../purchase_currency') === 'USD' ? 'USD' : 'TJS'),
                            ]),
                    ]),
            ]);
    }
}
