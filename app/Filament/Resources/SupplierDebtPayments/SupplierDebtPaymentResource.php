<?php

namespace App\Filament\Resources\SupplierDebtPayments;

use App\Filament\Resources\SupplierDebtPayments\Pages\CreateSupplierDebtPayment;
use App\Filament\Resources\SupplierDebtPayments\Pages\EditSupplierDebtPayment;
use App\Filament\Resources\SupplierDebtPayments\Pages\ListSupplierDebtPayments;
use App\Filament\Resources\SupplierDebtPayments\Schemas\SupplierDebtPaymentForm;
use App\Filament\Resources\SupplierDebtPayments\Tables\SupplierDebtPaymentsTable;
use App\Models\SupplierDebtPayment;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class SupplierDebtPaymentResource extends Resource
{
    protected static ?string $model = SupplierDebtPayment::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBanknotes;

    protected static UnitEnum|string|null $navigationGroup = 'Расчёты с поставщиками';

    protected static ?string $navigationLabel = 'Оплаты поставщикам';

    protected static ?string $modelLabel = 'Оплата поставщику';

    protected static ?string $pluralModelLabel = 'Оплаты поставщикам';

    protected static ?string $recordTitleAttribute = 'id';

    public static function form(Schema $schema): Schema
    {
        return SupplierDebtPaymentForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SupplierDebtPaymentsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSupplierDebtPayments::route('/'),
            'create' => CreateSupplierDebtPayment::route('/create'),
            'edit' => EditSupplierDebtPayment::route('/{record}/edit'),
        ];
    }
}
