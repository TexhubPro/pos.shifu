<?php

namespace App\Filament\Resources\ClientDebtPayments\Pages;

use App\Filament\Resources\ClientDebtPayments\ClientDebtPaymentResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListClientDebtPayments extends ListRecords
{
    protected static string $resource = ClientDebtPaymentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
