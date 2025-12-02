<?php

namespace App\Filament\Resources\ClientDebtPayments;

use App\Filament\Resources\ClientDebtPayments\Pages\CreateClientDebtPayment;
use App\Filament\Resources\ClientDebtPayments\Pages\EditClientDebtPayment;
use App\Filament\Resources\ClientDebtPayments\Pages\ListClientDebtPayments;
use App\Filament\Resources\ClientDebtPayments\Schemas\ClientDebtPaymentForm;
use App\Filament\Resources\ClientDebtPayments\Tables\ClientDebtPaymentsTable;
use App\Models\ClientDebtPayment;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class ClientDebtPaymentResource extends Resource
{
    protected static ?string $model = ClientDebtPayment::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCurrencyDollar;

    protected static UnitEnum|string|null $navigationGroup = 'Долги клиентов';

    protected static ?string $navigationLabel = 'Оплаты клиентов';

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'id';

    public static function form(Schema $schema): Schema
    {
        return ClientDebtPaymentForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ClientDebtPaymentsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListClientDebtPayments::route('/'),
            'create' => CreateClientDebtPayment::route('/create'),
            'edit' => EditClientDebtPayment::route('/{record}/edit'),
        ];
    }
}
