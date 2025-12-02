<?php

namespace App\Filament\Resources\ClientDebtPayments\Pages;

use App\Filament\Resources\ClientDebtPayments\ClientDebtPaymentResource;
use App\Services\CurrencyService;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditClientDebtPayment extends EditRecord
{
    protected static string $resource = ClientDebtPaymentResource::class;

    protected static ?string $title = 'Редактирование оплаты клиента';

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
