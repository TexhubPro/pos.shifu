<?php

namespace App\Filament\Resources\CurrencyRates;

use App\Filament\Resources\CurrencyRates\Pages\CreateCurrencyRate;
use App\Filament\Resources\CurrencyRates\Pages\EditCurrencyRate;
use App\Filament\Resources\CurrencyRates\Pages\ListCurrencyRates;
use App\Filament\Resources\CurrencyRates\Schemas\CurrencyRateForm;
use App\Filament\Resources\CurrencyRates\Tables\CurrencyRatesTable;
use App\Models\CurrencyRate;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class CurrencyRateResource extends Resource
{
    protected static ?string $model = CurrencyRate::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCurrencyDollar;

    protected static UnitEnum|string|null $navigationGroup = 'Финансы';

    protected static ?string $navigationLabel = 'Курс валют';

    protected static ?string $modelLabel = 'Запись курса валют';

    protected static ?string $pluralModelLabel = 'Записи курса валют';

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'currency_code';

    public static function form(Schema $schema): Schema
    {
        return CurrencyRateForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CurrencyRatesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCurrencyRates::route('/'),
            'create' => CreateCurrencyRate::route('/create'),
            'edit' => EditCurrencyRate::route('/{record}/edit'),
        ];
    }
}
