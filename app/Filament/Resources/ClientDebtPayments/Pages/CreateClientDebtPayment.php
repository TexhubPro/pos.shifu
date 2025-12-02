<?php

namespace App\Filament\Resources\ClientDebtPayments\Pages;

use App\Filament\Resources\ClientDebtPayments\ClientDebtPaymentResource;
use App\Services\CurrencyService;
use Filament\Resources\Pages\CreateRecord;

class CreateClientDebtPayment extends CreateRecord
{
    protected static string $resource = ClientDebtPaymentResource::class;

    protected static ?string $title = 'Новая оплата клиента';

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $currency = $data['currency'] ?? 'TJS';
        $amount = (float) ($data['amount'] ?? 0);

        if ($currency === 'USD') {
            $data['amount'] = app(CurrencyService::class)->usdToTjs($amount);
        }

        unset($data['currency']);

        if (blank($data['user_id'])) {
            $data['user_id'] = auth()->id();
        }

        return $data;
    }
}
