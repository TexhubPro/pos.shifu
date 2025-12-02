<?php

namespace App\Services;

use App\DataTransferObjects\SaleItemData;
use App\Models\Product;
use App\Models\Sale;
use App\Support\ReferenceGenerator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class SaleService
{
    public function __construct(
        private readonly InventoryService $inventoryService,
        private readonly DebtService $debtService,
    ) {
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     */
    public function create(array $saleData, array $items): Sale
    {
        return DB::transaction(function () use ($saleData, $items) {
            $sale = new Sale($saleData);
            $sale->reference = ReferenceGenerator::ensure($sale, 'SL');
            $this->hydrateMoneyFields($sale, $items);
            $sale->save();

            $this->syncItems($sale, $items);

            $this->finalize($sale);

            return $sale->fresh(['items.product', 'client']);
        });
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     */
    public function update(Sale $sale, array $saleData, array $items): Sale
    {
        return DB::transaction(function () use ($sale, $saleData, $items) {
            $sale->fill($saleData);
            $this->hydrateMoneyFields($sale, $items);
            $sale->save();

            $this->syncItems($sale, $items, true);

            $this->finalize($sale);

            return $sale->fresh(['items.product', 'client']);
        });
    }

    /**
     * @param  array<int, array<string, mixed>>  $itemPayloads
     */
    private function syncItems(Sale $sale, array $itemPayloads, bool $reset = false): void
    {
        if ($reset) {
            $sale->items()->delete();
        }

        $dtoItems = collect($itemPayloads)
            ->map(fn (array $payload) => SaleItemData::fromArray($payload));

        /** @var Collection<int, Product> $products */
        $products = Product::query()
            ->whereIn('id', $dtoItems->pluck('productId')->all())
            ->get()
            ->keyBy('id');

        foreach ($dtoItems as $item) {
            $product = $products->get($item->productId);

            if (! $product) {
                continue;
            }

            $sale->items()->create($item->toArray());
        }
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     */
    private function hydrateMoneyFields(Sale $sale, array $items): void
    {
        $itemsTotal = collect($items)->sum(function ($item) {
            $qty = (float) ($item['quantity'] ?? 0);
            $unitPrice = (float) ($item['unit_price'] ?? 0);
            $discount = (float) ($item['discount_amount'] ?? 0);

            return ($qty * $unitPrice) - $discount;
        });

        $sale->subtotal = $sale->subtotal ?? $itemsTotal;
        $sale->discount_amount = $sale->discount_amount ?? 0;
        $sale->delivery_fee = $sale->delivery_fee ?? 0;

        $sale->total = round(($sale->subtotal - $sale->discount_amount) + $sale->delivery_fee, 2);
        if ($sale->payment_method === 'debt') {
            $sale->paid_amount = 0;
            $sale->cash_amount = 0;
            $sale->card_amount = 0;
        } elseif ($sale->payment_method === 'mixed') {
            $sale->cash_amount = $sale->cash_amount ?? 0;
            $sale->card_amount = $sale->card_amount ?? 0;
            $sale->paid_amount = round($sale->cash_amount + $sale->card_amount, 2);
        } elseif ($sale->payment_method === 'card') {
            $sale->paid_amount = $sale->paid_amount ?? $sale->total;
            $sale->cash_amount = 0;
            $sale->card_amount = $sale->paid_amount;
        } else {
            $sale->paid_amount = $sale->paid_amount ?? $sale->total;
            $sale->cash_amount = $sale->paid_amount;
            $sale->card_amount = 0;
        }

        $sale->balance = max(round($sale->total - $sale->paid_amount, 2), 0);
        $sale->on_credit = $sale->balance > 0;
    }

    private function finalize(Sale $sale): void
    {
        if ($sale->status === 'completed') {
            $this->inventoryService->syncSaleInventory($sale);
        }

        if ($sale->client_id) {
            $this->debtService->refreshClientBalance($sale->client);
        }
    }
}
