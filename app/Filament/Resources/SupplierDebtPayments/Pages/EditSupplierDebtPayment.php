<?php

namespace App\Filament\Resources\SupplierDebtPayments\Pages;

use App\Filament\Resources\SupplierDebtPayments\SupplierDebtPaymentResource;
use App\Services\CurrencyService;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditSupplierDebtPayment extends EditRecord
{
    protected static string $resource = SupplierDebtPaymentResource::class;

    protected static ?string $title = 'Редактирование оплаты поставщику';

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

        if (blank($data['user_id'])) {
            $data['user_id'] = auth()->id();
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
