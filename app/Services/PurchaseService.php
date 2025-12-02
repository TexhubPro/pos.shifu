<?php

namespace App\Services;

use App\DataTransferObjects\PurchaseItemData;
use App\Models\Product;
use App\Models\Purchase;
use App\Support\ReferenceGenerator;
use Illuminate\Support\Facades\DB;

class PurchaseService
{
    public function __construct(
        private readonly InventoryService $inventoryService,
        private readonly CurrencyService $currencyService,
        private readonly DebtService $debtService,
    ) {
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     */
    public function create(array $purchaseData, array $items): Purchase
    {
        return DB::transaction(function () use ($purchaseData, $items) {
            $purchase = new Purchase($purchaseData);
            $purchase->reference = ReferenceGenerator::ensure($purchase, 'PO');
            $purchase->exchange_rate = $purchase->exchange_rate ?: $this->currencyService->getActiveRate();
            $this->hydrateMoneyFields($purchase, $items);
            $purchase->save();

            $this->syncItems($purchase, $items);
            $this->inventoryService->syncPurchaseInventory($purchase);

            if ($purchase->supplier_id) {
                $this->debtService->refreshSupplierBalance($purchase->supplier);
            }

            return $purchase->fresh(['items.product', 'supplier']);
        });
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     */
    public function update(Purchase $purchase, array $purchaseData, array $items): Purchase
    {
        return DB::transaction(function () use ($purchase, $purchaseData, $items) {
            $purchase->fill($purchaseData);
            $purchase->exchange_rate = $purchase->exchange_rate ?: $this->currencyService->getActiveRate();
            $this->hydrateMoneyFields($purchase, $items);
            $purchase->save();

            $purchase->items()->delete();
            $this->syncItems($purchase, $items);

            $this->inventoryService->syncPurchaseInventory($purchase);

            if ($purchase->supplier_id) {
                $this->debtService->refreshSupplierBalance($purchase->supplier);
            }

            return $purchase->fresh(['items.product', 'supplier']);
        });
    }

    private function hydrateMoneyFields(Purchase $purchase, array $items): void
    {
        $subtotal = collect($items)->sum(function ($item) use ($purchase) {
            $dto = PurchaseItemData::fromArray($item);

            $unitCost = $dto->unitCost;

            if ($dto->inputCurrency === 'USD') {
                $unitCost = $this->currencyService->usdToTjs($dto->inputUnitCost, $purchase->exchange_rate);
            }

            return $unitCost * $dto->quantity;
        });

        $purchase->subtotal = $purchase->subtotal ?? round($subtotal, 2);
        $purchase->discount_amount = $purchase->discount_amount ?? 0;
        $purchase->shipping_cost = $purchase->shipping_cost ?? 0;
        $purchase->paid_amount = $purchase->paid_amount ?? 0;

        $purchase->total = round(($purchase->subtotal - $purchase->discount_amount) + $purchase->shipping_cost, 2);
        $purchase->balance = max(round($purchase->total - $purchase->paid_amount, 2), 0);
        $purchase->payment_status = $this->resolvePaymentStatus($purchase->balance, $purchase->total);
        $purchase->is_credit = $purchase->balance > 0;
    }

    private function resolvePaymentStatus(float $balance, float $total): string
    {
        if ($balance <= 0) {
            return 'paid';
        }

        if ($balance >= $total) {
            return 'unpaid';
        }

        return 'partial';
    }

    /**
     * @param  array<int, array<string, mixed>>  $payloads
     */
    private function syncItems(Purchase $purchase, array $payloads): void
    {
        $products = Product::query()
            ->whereIn('id', collect($payloads)->pluck('product_id')->all())
            ->get()
            ->keyBy('id');

        foreach ($payloads as $payload) {
            $dto = PurchaseItemData::fromArray($payload);
            $product = $products->get($dto->productId);

            if (! $product) {
                continue;
            }

            $unitCost = $dto->unitCost;
            if ($dto->inputCurrency === 'USD') {
                $unitCost = $this->currencyService->usdToTjs($dto->inputUnitCost, $purchase->exchange_rate);
            }

            $totalCost = round($unitCost * $dto->quantity, 2);

            $purchase->items()->create([
                'product_id' => $dto->productId,
                'quantity' => $dto->quantity,
                'unit' => $dto->unit,
                'unit_cost' => $unitCost,
                'total_cost' => $totalCost,
                'input_currency' => $dto->inputCurrency,
                'input_unit_cost' => $dto->inputUnitCost,
                'exchange_rate' => $dto->exchangeRate ?: $purchase->exchange_rate,
            ]);

            $product->forceFill([
                'cost_price' => $unitCost,
            ])->save();
        }
    }
}
