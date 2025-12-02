<?php

namespace App\Filament\Resources\SupplierDebtPayments\Pages;

use App\Filament\Resources\SupplierDebtPayments\SupplierDebtPaymentResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSupplierDebtPayments extends ListRecords
{
    protected static string $resource = SupplierDebtPaymentResource::class;

    protected static ?string $title = 'Оплаты поставщикам';

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Добавить оплату'),
        ];
    }
}
