<?php

namespace App\Filament\Resources\Suppliers\Pages;

use App\Filament\Resources\Suppliers\SupplierResource;
use App\Models\Product;
use App\Models\Supplier;
use App\Services\CurrencyService;
use App\Services\PurchaseService;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;

class ListSuppliers extends ListRecords
{
    protected static string $resource = SupplierResource::class;

    protected static ?string $title = 'Поставщики';

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Добавить поставщика'),
            Action::make('addSupplierDebt')
                ->label('Добавить долг поставщику')
                ->icon('heroicon-o-banknotes')
                ->form([
                    Select::make('supplier_id')
                        ->label('Поставщик')
                        ->options(fn () => Supplier::query()->orderBy('name')->pluck('name', 'id'))
                        ->searchable()
                        ->required(),
                    Select::make('product_id')
                        ->label('Товар')
                        ->options(fn () => Product::query()->orderBy('name')->pluck('name', 'id'))
                        ->searchable()
                        ->required(),
                    TextInput::make('quantity')
                        ->label('Количество')
                        ->numeric()
                        ->default(1)
                        ->minValue(0.01)
                        ->required(),
                    Select::make('currency')
                        ->label('Валюта')
                        ->options([
                            'TJS' => 'Сомони (TJS)',
                            'USD' => 'Доллар (USD)',
                        ])
                        ->default('TJS')
                        ->required(),
                    TextInput::make('unit_cost')
                        ->label('Цена за единицу')
                        ->numeric()
                        ->required(),
                    Textarea::make('notes')
                        ->label('Комментарий')
                        ->rows(2),
                ])
                ->action(function (array $data) {
                    $items = [[
                        'product_id' => $data['product_id'],
                        'quantity' => $data['quantity'],
                        'unit' => 'шт',
                        'unit_cost' => $data['unit_cost'],
                        'input_currency' => $data['currency'],
                        'input_unit_cost' => $data['unit_cost'],
                    ]];

                    $purchaseData = [
                        'supplier_id' => $data['supplier_id'],
                        'user_id' => auth()->id(),
                        'purchased_at' => now(),
                        'exchange_rate' => app(CurrencyService::class)->getActiveRate(),
                        'paid_amount' => 0,
                        'payment_method' => 'credit',
                        'notes' => $data['notes'] ?? null,
                    ];

                    app(PurchaseService::class)->create($purchaseData, $items);

                    Notification::make()
                        ->title('Долг поставщику создан')
                        ->success()
                        ->send();
                }),
        ];
    }
}
