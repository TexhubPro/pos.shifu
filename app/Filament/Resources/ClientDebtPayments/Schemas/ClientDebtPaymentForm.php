<?php

namespace App\Filament\Resources\ClientDebtPayments\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class ClientDebtPaymentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('client_id')
                    ->label('Клиент')
                    ->relationship('client', 'name')
                    ->searchable()
                    ->required(),
                Select::make('sale_id')
                    ->label('Продажа')
                    ->relationship('sale', 'reference')
                    ->searchable()
                    ->nullable(),
                Select::make('user_id')
                    ->label('Ответственный')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->default(fn () => auth()->id())
                    ->required(),
                DateTimePicker::make('paid_at')
                    ->label('Дата оплаты')
                    ->default(now())
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
                    ->required()
                    ->default('cash')
                    ->options([
                        'cash' => 'Наличные',
                        'card' => 'Карта',
                        'bank' => 'Банк',
                    ]),
                Textarea::make('notes')
                    ->label('Комментарий')
                    ->columnSpanFull(),
            ]);
    }
}
