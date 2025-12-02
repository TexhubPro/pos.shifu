<?php

namespace App\Filament\Resources\SupplierDebtPayments\Pages;

use App\Filament\Resources\SupplierDebtPayments\SupplierDebtPaymentResource;
use App\Services\CurrencyService;
use Filament\Resources\Pages\CreateRecord;

class CreateSupplierDebtPayment extends CreateRecord
{
    protected static string $resource = SupplierDebtPaymentResource::class;

    protected static ?string $title = 'Новая оплата поставщику';

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
