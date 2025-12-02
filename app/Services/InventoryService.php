<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\StockMovement;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class InventoryService
{
    public function adjust(
        Product $product,
        float $quantity,
        string $type,
        ?Model $reference = null,
        ?string $comment = null,
        array $meta = []
    ): StockMovement {
        return DB::transaction(function () use ($product, $quantity, $type, $reference, $comment, $meta) {
            $newStockLevel = (float) $product->stock + $quantity;

            if (! config('pos.pos.allow_negative_stock', false) && $newStockLevel < 0) {
                throw new RuntimeException(__('Недостаточно товара на складе.'));
            }

            $product->forceFill([
                'stock' => $newStockLevel,
            ])->save();

            return $product->stockMovements()->create([
                'type' => $type,
                'quantity' => $quantity,
                'stock_after' => $newStockLevel,
                'referenceable_id' => $reference?->getKey(),
                'referenceable_type' => $reference?->getMorphClass(),
                'comment' => $comment,
                'occurred_at' => now(),
                'meta' => $meta,
            ]);
        });
    }

    public function increase(Product $product, float $quantity, ?Model $reference = null, ?string $comment = null): StockMovement
    {
        return $this->adjust($product, $quantity, 'purchase', $reference, $comment);
    }

    public function decrease(Product $product, float $quantity, ?Model $reference = null, ?string $comment = null): StockMovement
    {
        return $this->adjust($product, -1 * abs($quantity), 'sale', $reference, $comment);
    }

    public function syncPurchaseInventory(Purchase $purchase): void
    {
        $this->resetReference($purchase);
        $purchase->loadMissing('items.product');

        foreach ($purchase->items as $item) {
            $this->increase(
                $item->product,
                (float) $item->quantity,
                $purchase,
                sprintf('Закупка %s', $purchase->reference)
            );
        }
    }

    public function syncSaleInventory(Sale $sale): void
    {
        $this->resetReference($sale);
        $sale->loadMissing('items.product');

        foreach ($sale->items as $item) {
            $this->decrease(
                $item->product,
                (float) $item->quantity,
                $sale,
                sprintf('Продажа %s', $sale->reference)
            );
        }
    }

    public function resetReference(Model $reference): void
    {
        $movements = StockMovement::query()
            ->where('referenceable_type', $reference->getMorphClass())
            ->where('referenceable_id', $reference->getKey())
            ->get();

        foreach ($movements as $movement) {
            $product = $movement->product;

            if ($product) {
                $product->forceFill([
                    'stock' => $product->stock - $movement->quantity,
                ])->save();
            }

            $movement->delete();
        }
    }
}
