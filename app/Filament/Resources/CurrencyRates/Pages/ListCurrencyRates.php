<?php

namespace App\Filament\Resources\CurrencyRates\Pages;

use App\Filament\Resources\CurrencyRates\CurrencyRateResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCurrencyRates extends ListRecords
{
    protected static string $resource = CurrencyRateResource::class;

    protected static ?string $title = 'Курсы валют';

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Добавить курс'),
        ];
    }
}
