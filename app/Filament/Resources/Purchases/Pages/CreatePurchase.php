<?php

namespace App\Filament\Resources\Purchases\Pages;

use App\Filament\Resources\Purchases\PurchaseResource;
use App\Models\Purchase;
use App\Services\CurrencyService;
use App\Services\PurchaseService;
use Filament\Resources\Pages\CreateRecord;

class CreatePurchase extends CreateRecord
{
    protected static string $resource = PurchaseResource::class;

    protected function handleRecordCreation(array $data): Purchase
    {
        [$purchaseData, $items] = $this->preparePayload($data);

        return app(PurchaseService::class)->create($purchaseData, $items);
    }

    /**
     * @return array{0: array<string, mixed>, 1: array<int, array<string, mixed>>}
     */
    protected function preparePayload(array $data): array
    {
        $currency = $data['purchase_currency'] ?? 'TJS';
        $currencyService = app(CurrencyService::class);
        $exchangeRate = $currency === 'USD'
            ? $currencyService->getActiveRate()
            : 1;

        $items = collect($data['items'] ?? [])
            ->map(function (array $item) use ($currency, $exchangeRate, $currencyService) {
                $unitPrice = (float) ($item['unit_price'] ?? 0);

                return [
                    'product_id' => $item['product_id'],
                    'quantity' => (float) ($item['quantity'] ?? 0),
                    'unit' => 'шт',
                    'unit_cost' => $currency === 'USD'
                        ? $currencyService->usdToTjs($unitPrice, $exchangeRate)
                        : $unitPrice,
                    'input_currency' => $currency,
                    'input_unit_cost' => $unitPrice,
                    'exchange_rate' => $exchangeRate,
                ];
            })
            ->toArray();

        $data['exchange_rate'] = $exchangeRate;
        unset($data['items'], $data['purchase_currency']);

        return [$data, $items];
    }
}
