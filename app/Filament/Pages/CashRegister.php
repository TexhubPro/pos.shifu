<?php

namespace App\Filament\Pages;

use App\Models\Client;
use App\Models\ClientDebtPayment;
use App\Models\Product;
use App\Services\CurrencyService;
use App\Services\SaleService;
use BackedEnum;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Collection;
use UnitEnum;

class CashRegister extends Page implements HasForms
{
    use InteractsWithForms;

    protected static BackedEnum|string|null $navigationIcon = Heroicon::OutlinedCalculator;

    protected static UnitEnum|string|null $navigationGroup = 'Продажи';

    protected static ?string $navigationLabel = 'Касса';

    protected static ?int $navigationSort = 0;

    protected string $view = 'filament.pages.cash-register';

    public ?array $data = [];

    public string $productSearch = '';

    public function mount(): void
    {
        $this->form->fill($this->getDefaultFormState());
        $this->ensureItemsArray();
    }

    public function form(Schema $form): Schema
    {
        return $form
            ->schema($this->getFormSchema())
            ->statePath('data');
    }

    protected function getFormSchema(): array
    {
        return [
            Section::make('Параметры продажи')
                ->columns(2)
                ->schema([
                    Select::make('client_id')
                        ->label('Клиент')
                        ->options(fn(): Collection => Client::query()->orderBy('name')->pluck('name', 'id'))
                        ->searchable()
                        ->preload()
                        ->columnSpanFull()
                        ->nullable(),
                    Toggle::make('create_client')
                        ->label('Создать нового клиента')
                        ->columnSpanFull()

                        ->live(),

                    TextInput::make('new_client_name')
                        ->label('Имя клиента')
                        ->required(fn(callable $get) => $get('create_client'))
                        ->visible(fn(callable $get) => $get('create_client'))
                        ->maxLength(255),
                    TextInput::make('new_client_phone')
                        ->label('Телефон клиента')
                        ->visible(fn(callable $get) => $get('create_client'))
                        ->tel()
                        ->maxLength(255),
                    DateTimePicker::make('sold_at')
                        ->label('Дата продажи')
                        ->seconds(false)
                        ->required()
                        ->columnSpanFull(),
                    Select::make('currency')
                        ->label('Валюта')
                        ->options([
                            'TJS' => 'Сомони (TJS)',
                            'USD' => 'Доллар (USD)',
                        ])
                        ->default('TJS')
                        ->live()
                        ->required(),
                    TextInput::make('discount_amount')
                        ->label('Скидка')
                        ->numeric()
                        ->default(0)
                        ->prefix(fn(callable $get) => $get('currency')),
                    TextInput::make('delivery_fee')
                        ->label('Доставка')
                        ->numeric()
                        ->default(0)
                        ->prefix(fn(callable $get) => $get('currency')),
                    Textarea::make('notes')
                        ->label('Комментарий')
                        ->rows(2)
                        ->columnSpanFull(),
                ]),
            Section::make('Оплата')
                ->columns(3)
                ->schema([
                    Select::make('payment_method')
                        ->label('Способ оплаты')
                        ->options([
                            'cash' => 'Наличные',
                            'card' => 'Карта',
                            'mixed' => 'Смешанная',
                            'debt' => 'В долг',
                        ])
                        ->required()
                        ->default('cash'),
                    TextInput::make('cash_amount')
                        ->label('Наличные')
                        ->numeric()
                        ->default(0)
                        ->prefix(fn(callable $get) => $get('currency')),
                    TextInput::make('card_amount')
                        ->label('Безнал')
                        ->numeric()
                        ->default(0)
                        ->prefix(fn(callable $get) => $get('currency')),
                    Toggle::make('on_credit')
                        ->label('Продажа в долг')
                        ->inline(false)
                        ->reactive(),
                    TextInput::make('existing_debt_payment')
                        ->label('Погашение текущего долга')
                        ->numeric()
                        ->default(0)
                        ->prefix(fn(callable $get) => $get('currency'))
                        ->visible(fn(callable $get) => filled($get('client_id'))),
                    DatePicker::make('due_at')
                        ->label('Срок оплаты долга')
                        ->visible(fn($get) => (bool) $get('on_credit')),
                ]),
        ];
    }

    protected function ensureItemsArray(): void
    {
        if (! is_array($this->data['items'] ?? null)) {
            $this->data['items'] = [];
        }
    }

    public function getProductsProperty(): Collection
    {
        $query = Product::query()->select('id', 'name', 'sale_price', 'stock');

        if ($search = trim($this->productSearch)) {
            $query->where(
                fn($q) => $q
                    ->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%")
            );
        }

        return $query->orderBy('name')->limit(100)->get();
    }

    public function addProductToCart(int $productId): void
    {
        $product = Product::query()->select('id', 'name', 'sale_price')->find($productId);

        if (! $product) {
            Notification::make()
                ->title('Товар не найден')
                ->danger()
                ->send();

            return;
        }

        $this->ensureItemsArray();

        foreach ($this->data['items'] as $index => $item) {
            if ((int) ($item['product_id'] ?? 0) === $product->id) {
                $this->data['items'][$index]['quantity'] = ((float) ($item['quantity'] ?? 0)) + 1;
                $this->syncItem($index);

                return;
            }
        }

        $this->data['items'][] = [
            'product_id' => $product->id,
            'product_name' => $product->name,
            'quantity' => 1,
            'unit' => 'шт',
            'unit_price' => (float) $product->sale_price,
            'discount_amount' => 0,
        ];
    }

    public function increaseItemQuantity(int $index): void
    {
        if (! isset($this->data['items'][$index])) {
            return;
        }

        $this->data['items'][$index]['quantity'] = ((float) ($this->data['items'][$index]['quantity'] ?? 0)) + 1;
        $this->syncItem($index);
    }

    public function decreaseItemQuantity(int $index): void
    {
        if (! isset($this->data['items'][$index])) {
            return;
        }

        $newQuantity = ((float) ($this->data['items'][$index]['quantity'] ?? 0)) - 1;

        if ($newQuantity <= 0) {
            $this->removeItem($index);

            return;
        }

        $this->data['items'][$index]['quantity'] = $newQuantity;
        $this->syncItem($index);
    }

    public function removeItem(int $index): void
    {
        if (! isset($this->data['items'][$index])) {
            return;
        }

        array_splice($this->data['items'], $index, 1);
    }

    public function syncItem(int $index): void
    {
        if (! isset($this->data['items'][$index])) {
            return;
        }

        $item = &$this->data['items'][$index];
        $item['quantity'] = max((float) ($item['quantity'] ?? 0), 0);
        $item['unit_price'] = max((float) ($item['unit_price'] ?? 0), 0);
        $item['discount_amount'] = max((float) ($item['discount_amount'] ?? 0), 0);
        $item['unit'] = $item['unit'] ?? 'шт';

        $lineTotal = $item['quantity'] * $item['unit_price'];
        if ($item['discount_amount'] > $lineTotal) {
            $item['discount_amount'] = $lineTotal;
        }
    }

    public function getItemTotal(int $index): float
    {
        if (! isset($this->data['items'][$index])) {
            return 0;
        }

        $item = $this->data['items'][$index];

        return max(
            ((float) ($item['quantity'] ?? 0)) * ((float) ($item['unit_price'] ?? 0)) - ((float) ($item['discount_amount'] ?? 0)),
            0
        );
    }

    protected function getDefaultFormState(): array
    {
        return [
            'sold_at' => now(),
            'currency' => 'TJS',
            'payment_method' => 'cash',
            'cash_amount' => 0,
            'card_amount' => 0,
            'discount_amount' => 0,
            'delivery_fee' => 0,
            'on_credit' => false,
            'due_at' => null,
            'create_client' => false,
            'new_client_name' => null,
            'new_client_phone' => null,
            'existing_debt_payment' => 0,
            'items' => [],
        ];
    }

    public function resetForm(): void
    {
        $this->form->fill($this->getDefaultFormState());
        $this->ensureItemsArray();
    }

    public function submit(): void
    {
        $this->ensureItemsArray();

        if (empty($this->data['items'])) {
            Notification::make()
                ->title('Добавьте хотя бы один товар')
                ->warning()
                ->send();

            return;
        }

        $state = $this->form->getState();
        $items = $this->data['items'];
        unset($state['items']);

        if (($state['create_client'] ?? false) && filled($state['new_client_name'])) {
            $client = Client::create([
                'name' => $state['new_client_name'],
                'phone' => $state['new_client_phone'],
            ]);
            $state['client_id'] = $client->id;
        }

        $existingDebtPayment = (float) ($state['existing_debt_payment'] ?? 0);
        $currency = $state['currency'] ?? 'TJS';
        $rate = app(CurrencyService::class)->getActiveRate();

        $convert = function (float $value) use ($currency, $rate): float {
            if ($currency === 'USD') {
                return app(CurrencyService::class)->usdToTjs($value, $rate);
            }

            return round($value, 2);
        };

        foreach (['discount_amount', 'delivery_fee', 'cash_amount', 'card_amount', 'existing_debt_payment'] as $field) {
            $state[$field] = $convert((float) ($state[$field] ?? 0));
        }
        $existingDebtPayment = $state['existing_debt_payment'];

        foreach ($items as &$item) {
            $item['unit_price'] = $convert((float) ($item['unit_price'] ?? 0));
            $item['discount_amount'] = $convert((float) ($item['discount_amount'] ?? 0));
        }
        unset($item);

        $state['user_id'] = auth()->id();
        $state['meta'] = array_merge($state['meta'] ?? [], [
            'currency' => $currency,
            'rate' => $rate,
        ]);

        unset(
            $state['create_client'],
            $state['new_client_name'],
            $state['new_client_phone'],
            $state['existing_debt_payment'],
            $state['currency']
        );

        $sale = app(SaleService::class)->create($state, $items);

        if ($existingDebtPayment > 0 && $sale->client_id) {
            ClientDebtPayment::create([
                'client_id' => $sale->client_id,
                'sale_id' => null,
                'user_id' => auth()->id(),
                'paid_at' => now(),
                'amount' => $existingDebtPayment,
                'method' => 'cash',
                'notes' => 'Оплата долга через кассу',
            ]);
        }

        Notification::make()
            ->title('Продажа оформлена')
            ->body('Чек успешно сохранён.')
            ->success()
            ->send();

        $this->resetForm();
    }

    public function getHeading(): string|Htmlable
    {
        return 'Касса';
    }

    public function getItemsCountProperty(): int
    {
        return count($this->data['items'] ?? []);
    }

    public function getItemsQuantityProperty(): float
    {
        return collect($this->data['items'] ?? [])->sum(fn($item) => (float) ($item['quantity'] ?? 0));
    }

    public function getTotalAmountProperty(): float
    {
        $itemsTotal = collect($this->data['items'] ?? [])->sum(function (array $item): float {
            $quantity = (float) ($item['quantity'] ?? 0);
            $unitPrice = (float) ($item['unit_price'] ?? 0);
            $discount = (float) ($item['discount_amount'] ?? 0);

            return ($quantity * $unitPrice) - $discount;
        });

        $delivery = (float) ($this->data['delivery_fee'] ?? 0);
        $saleDiscount = (float) ($this->data['discount_amount'] ?? 0);

        return max(round($itemsTotal - $saleDiscount + $delivery, 2), 0);
    }
}
