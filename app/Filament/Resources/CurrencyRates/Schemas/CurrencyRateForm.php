<?php

namespace App\Filament\Resources\CurrencyRates\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class CurrencyRateForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('currency_code')
                    ->label('Код валюты')
                    ->options([
                        'USD' => 'USD — Доллар США',
                    ])
                    ->required()
                    ->default('USD'),
                TextInput::make('rate')
                    ->label('Курс (TJS за 1 единицу)')
                    ->placeholder('Например: 10.90')
                    ->required()
                    ->numeric()
                    ->suffix('TJS'),
                DateTimePicker::make('effective_at')
                    ->label('Действует с')
                    ->seconds(false)
                    ->required(),
                Toggle::make('is_active')
                    ->label('Использовать как текущий')
                    ->helperText('При активации предыдущие курсы будут отключены.')
                    ->required(),
                Textarea::make('notes')
                    ->label('Комментарий')
                    ->placeholder('Например: Курс от банка на сегодня')
                    ->columnSpanFull(),
            ]);
    }
}
