<x-filament-panels::page>
    @vite('resources/css/app.css')
    <div class="grid gap-6 lg:grid-cols-5">
        <div class="space-y-4 lg:col-span-1">
            <x-filament::section heading="Список товаров">
                <x-filament::input.wrapper>
                    <x-filament::input type="search" wire:model.debounce.400ms="productSearch"
                        placeholder="Поиск по названию или артикулу..." />
                </x-filament::input.wrapper>

                <div class="mt-4 max-h-[70vh] space-y-2 overflow-y-auto">
                    @forelse ($this->products as $product)
                    <button type="button" wire:click="addProductToCart({{ $product->id }})"
                        class="flex w-full items-center justify-between rounded-xl border border-gray-200 px-3 py-2 text-left transition hover:border-primary-500 dark:border-gray-700">
                        <div>
                            <p class="font-medium text-sm">{{ $product->name }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                В наличии: {{ number_format($product->stock, 2, '.', ' ') }} шт.
                            </p>
                        </div>
                        <div class="text-sm font-semibold text-primary-600">
                            {{ number_format($product->sale_price, 2, '.', ' ') }} TJS
                        </div>
                    </button>
                    @empty
                    <p class="text-sm text-gray-500">Ничего не найдено.</p>
                    @endforelse
                </div>
            </x-filament::section>
        </div>

        <div class="space-y-6 lg:col-span-2">
            <x-filament::section heading="Корзина">
                @if (empty($this->data['items']))
                <p class="text-sm text-gray-500">Добавьте товар из списка слева, чтобы оформить продажу.</p>
                @else
                <div class="space-y-4">
                    @foreach ($this->data['items'] as $index => $item)
                    <div class="rounded-xl border border-gray-200 p-3 dark:border-gray-700"
                        wire:key="cart-item-{{ $index }}">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="font-semibold text-sm">
                                    {{ $item['product_name'] ?? 'Товар' }}
                                </p>
                                <p class="text-xs text-gray-500">
                                    Цена по умолчанию: {{ number_format($item['unit_price'] ?? 0, 2, '.', ' ') }} TJS
                                </p>
                            </div>
                            <x-filament::icon-button color="gray" icon="heroicon-o-x-mark" label="Удалить"
                                wire:click="removeItem({{ $index }})" />
                        </div>

                        <div class="mt-3 grid gap-3 sm:grid-cols-3">
                            <div class="space-y-1">
                                <span class="text-xs text-gray-600">Количество</span>
                                <div class="flex items-center gap-2">
                                    <x-filament::icon-button color="gray" icon="heroicon-o-minus"
                                        wire:click="decreaseItemQuantity({{ $index }})" size="sm" />
                                    <x-filament::input.wrapper class="w-full">
                                        <x-filament::input type="number" step="0.01" min="0"
                                            wire:model.lazy="data.items.{{ $index }}.quantity"
                                            wire:change="syncItem({{ $index }})" />
                                    </x-filament::input.wrapper>
                                    <x-filament::icon-button color="primary" icon="heroicon-o-plus"
                                        wire:click="increaseItemQuantity({{ $index }})" size="sm" />
                                </div>
                                <p class="text-xs text-gray-500">Ед.: {{ $item['unit'] ?? 'шт' }}</p>
                            </div>

                            <div class="space-y-1">
                                <span class="text-xs text-gray-600">Цена, TJS</span>
                                <x-filament::input.wrapper>
                                    <x-filament::input type="number" min="0" step="0.01"
                                        wire:model.lazy="data.items.{{ $index }}.unit_price"
                                        wire:change="syncItem({{ $index }})" />
                                </x-filament::input.wrapper>
                            </div>

                            <div class="space-y-1">
                                <span class="text-xs text-gray-600">Скидка, TJS</span>
                                <x-filament::input.wrapper>
                                    <x-filament::input type="number" min="0" step="0.01"
                                        wire:model.lazy="data.items.{{ $index }}.discount_amount"
                                        wire:change="syncItem({{ $index }})" />
                                </x-filament::input.wrapper>
                            </div>
                        </div>

                        <div class="mt-3 flex items-center justify-between text-sm font-semibold">
                            <span>Итого по позиции</span>
                            <span>{{ number_format($this->getItemTotal($index), 2, '.', ' ') }} TJS</span>
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif
            </x-filament::section>


        </div>
        <div class="space-y-6 lg:col-span-2">
            {{ $this->form }}

            <x-filament::section heading="Итоги">
                <dl class="space-y-2 text-sm">
                    <div class="flex items-center justify-between">
                        <dt>Позиции</dt>
                        <dd>{{ $this->itemsCount }}</dd>
                    </div>
                    <div class="flex items-center justify-between">
                        <dt>Количество товаров</dt>
                        <dd>{{ number_format($this->itemsQuantity, 2, '.', ' ') }}</dd>
                    </div>
                    <div class="flex items-center justify-between text-base font-semibold">
                        <dt>К оплате</dt>
                        <dd>{{ number_format($this->totalAmount, 2, '.', ' ') }} TJS</dd>
                    </div>
                </dl>
            </x-filament::section>

            <div class="flex flex-col gap-2 sm:flex-row sm:justify-end">
                <x-filament::button type="button" color="gray" wire:click="resetForm">
                    Очистить форму
                </x-filament::button>

                <x-filament::button type="button" wire:click="submit" icon="heroicon-o-check-circle">
                    Оформить продажу
                </x-filament::button>
            </div>


        </div>
    </div>
</x-filament-panels::page>