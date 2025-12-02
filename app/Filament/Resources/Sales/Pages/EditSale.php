<?php

namespace App\Filament\Resources\Sales\Pages;

use App\Filament\Resources\Sales\SaleResource;
use App\Models\Sale;
use App\Services\SaleService;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditSale extends EditRecord
{
    protected static string $resource = SaleResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['items'] = $this->record->items()
            ->get()
            ->map(fn ($item) => [
                'product_id' => $item->product_id,
                'quantity' => $item->quantity,
                'unit' => $item->unit,
                'unit_price' => $item->unit_price,
                'discount_amount' => $item->discount_amount,
            ])
            ->toArray();

        return $data;
    }

    protected function handleRecordUpdate(Model $record, array $data): Sale
    {
        $items = $data['items'] ?? [];
        unset($data['items']);

        return app(SaleService::class)->update($record, $data, $items);
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
