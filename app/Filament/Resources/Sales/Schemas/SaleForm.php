<?php

namespace App\Filament\Resources\Sales\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class SaleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Hidden::make('user_id')
                    ->default(fn () => auth()->id()),
                Section::make('Продажа')
                    ->columns(2)
                    ->schema([
                        Select::make('client_id')
                            ->label('Клиент')
                            ->relationship('client', 'name')
                            ->searchable()
                            ->preload()
                            ->nullable(),
                        DateTimePicker::make('sold_at')
                            ->label('Дата продажи')
                            ->seconds(false)
                            ->required(),
                        TextInput::make('discount_amount')
                            ->label('Скидка (TJS)')
                            ->numeric()
                            ->default(0)
                            ->prefix('SM'),
                        TextInput::make('delivery_fee')
                            ->label('Доставка (TJS)')
                            ->numeric()
                            ->default(0)
                            ->prefix('SM'),
                        Textarea::make('delivery_details')
                            ->label('Детали доставки')
                            ->columnSpanFull()
                            ->rows(2),
                        Textarea::make('notes')
                            ->label('Комментарий')
                            ->columnSpanFull()
                            ->rows(2),
                    ]),
                Section::make('Оплата')
                    ->columns(3)
                    ->schema([
                        Select::make('payment_method')
                            ->label('Способ оплаты')
                            ->options([
                                'cash' => 'Наличные',
                                'card' => 'Карта',
                                'mixed' => 'Смешанная',
                                'debt' => 'В долг',
                            ])
                            ->required()
                            ->default('cash'),
                        TextInput::make('cash_amount')
                            ->label('Наличные (TJS)')
                            ->numeric()
                            ->default(0)
                            ->prefix('SM'),
                        TextInput::make('card_amount')
                            ->label('Безнал (TJS)')
                            ->numeric()
                            ->default(0)
                            ->prefix('SM'),
                        Toggle::make('on_credit')
                            ->label('Продажа в долг')
                            ->inline(false)
                            ->reactive(),
                        DatePicker::make('due_at')
                            ->label('Срок оплаты')
                            ->visible(fn ($get) => (bool) $get('on_credit')),
                    ]),
                Section::make('Позиции')
                    ->schema([
                        Repeater::make('items')
                            ->label('Товары')
                            ->minItems(1)
                            ->defaultItems(1)
                            ->columns(5)
                            ->schema([
                                Select::make('product_id')
                                    ->label('Товар')
                                    ->relationship('product', 'name')
                                    ->required()
                                    ->searchable()
                                    ->preload(),
                                TextInput::make('quantity')
                                    ->label('Кол-во')
                                    ->numeric()
                                    ->default(1)
                                    ->required(),
                                TextInput::make('unit')
                                    ->label('Ед.')
                                    ->default('pcs'),
                                TextInput::make('unit_price')
                                    ->label('Цена (TJS)')
                                    ->numeric()
                                    ->required()
                                    ->prefix('SM'),
                                TextInput::make('discount_amount')
                                    ->label('Скидка')
                                    ->numeric()
                                    ->default(0)
                                    ->prefix('SM'),
                            ]),
                    ]),
            ]);
    }
}
