<?php

namespace App\DataTransferObjects;

readonly class SaleItemData
{
    public function __construct(
        public int $productId,
        public float $quantity,
        public float $unitPrice,
        public float $discountAmount = 0.0,
        public float $totalPrice = 0.0,
        public string $unit = 'pcs',
    ) {
    }

    public static function fromArray(array $data): self
    {
        $quantity = (float) ($data['quantity'] ?? 0);
        $unitPrice = (float) ($data['unit_price'] ?? 0);
        $discount = (float) ($data['discount_amount'] ?? 0);
        $total = (float) ($data['total_price'] ?? ($quantity * $unitPrice) - $discount);

        return new self(
            productId: (int) $data['product_id'],
            quantity: $quantity,
            unitPrice: $unitPrice,
            discountAmount: $discount,
            totalPrice: $total,
            unit: $data['unit'] ?? 'pcs',
        );
    }

    public function toArray(): array
    {
        return [
            'product_id' => $this->productId,
            'quantity' => $this->quantity,
            'unit' => $this->unit,
            'unit_price' => $this->unitPrice,
            'discount_amount' => $this->discountAmount,
            'total_price' => $this->totalPrice,
        ];
    }
}
