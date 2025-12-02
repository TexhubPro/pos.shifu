<?php

namespace App\Filament\Resources\Expenses\Pages;

use App\Filament\Resources\Expenses\ExpenseResource;
use App\Services\CurrencyService;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditExpense extends EditRecord
{
    protected static string $resource = ExpenseResource::class;

    protected static ?string $title = 'Редактирование расхода';

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['currency'] = 'TJS';

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $currency = $data['currency'] ?? 'TJS';
        $amount = (float) ($data['amount'] ?? 0);

        if ($currency === 'USD') {
            $data['amount'] = app(CurrencyService::class)->usdToTjs($amount);
        }

        unset($data['currency']);

        if (($data['type'] ?? null) !== 'delivery') {
            $data['delivery_products'] = null;
        }

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->label('Удалить'),
        ];
    }
}
