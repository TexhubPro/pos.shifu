<?php

namespace App\DataTransferObjects;

readonly class PurchaseItemData
{
    public function __construct(
        public int $productId,
        public float $quantity,
        public float $unitCost,
        public string $unit = 'pcs',
        public string $inputCurrency = 'TJS',
        public float $inputUnitCost = 0.0,
        public float $exchangeRate = 1.0,
        public float $totalCost = 0.0,
    ) {
    }

    public static function fromArray(array $data): self
    {
        $quantity = (float) ($data['quantity'] ?? 0);
        $unitCost = (float) ($data['unit_cost'] ?? ($data['input_unit_cost'] ?? 0));
        $total = (float) ($data['total_cost'] ?? $quantity * $unitCost);

        return new self(
            productId: (int) $data['product_id'],
            quantity: $quantity,
            unitCost: $unitCost,
            unit: $data['unit'] ?? 'pcs',
            inputCurrency: $data['input_currency'] ?? 'TJS',
            inputUnitCost: (float) ($data['input_unit_cost'] ?? $unitCost),
            exchangeRate: (float) ($data['exchange_rate'] ?? 1),
            totalCost: $total,
        );
    }

    public function toArray(): array
    {
        return [
            'product_id' => $this->productId,
            'quantity' => $this->quantity,
            'unit' => $this->unit,
            'unit_cost' => $this->unitCost,
            'total_cost' => $this->totalCost,
            'input_currency' => $this->inputCurrency,
            'input_unit_cost' => $this->inputUnitCost,
            'exchange_rate' => $this->exchangeRate,
        ];
    }
}
