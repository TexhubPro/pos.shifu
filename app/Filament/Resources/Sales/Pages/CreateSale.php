<?php

namespace App\Filament\Resources\Sales\Pages;

use App\Filament\Resources\Sales\SaleResource;
use App\Models\Sale;
use App\Services\SaleService;
use Filament\Resources\Pages\CreateRecord;

class CreateSale extends CreateRecord
{
    protected static string $resource = SaleResource::class;

    protected function handleRecordCreation(array $data): Sale
    {
        $items = $data['items'] ?? [];
        unset($data['items']);

        return app(SaleService::class)->create($data, $items);
    }
}
