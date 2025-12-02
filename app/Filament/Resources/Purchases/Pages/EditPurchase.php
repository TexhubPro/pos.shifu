<?php

namespace App\Filament\Resources\Purchases\Pages;

use App\Filament\Resources\Purchases\PurchaseResource;
use App\Models\Purchase;
use App\Services\CurrencyService;
use App\Services\PurchaseService;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditPurchase extends EditRecord
{
    protected static string $resource = PurchaseResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $items = $this->record->items()->get();
        $currency = $items->pluck('input_currency')->filter()->first() ?? 'TJS';

        $data['purchase_currency'] = $currency;
        $data['items'] = $items
            ->map(fn ($item) => [
                'product_id' => $item->product_id,
                'quantity' => $item->quantity,
                'unit_price' => $item->input_currency === 'USD'
                    ? $item->input_unit_cost
                    : $item->unit_cost,
            ])
            ->toArray();

        return $data;
    }

    protected function handleRecordUpdate(Model $record, array $data): Purchase
    {
        [$purchaseData, $items] = $this->preparePayload($data);

        return app(PurchaseService::class)->update($record, $purchaseData, $items);
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
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
