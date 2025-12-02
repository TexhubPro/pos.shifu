<x-filament-panels::page>
    @vite('resources/css/app.css')

    @php
        $salesChart = $this->getSalesVsExpensesChart();
        $debtChart = $this->getDebtBreakdown();
        $topProducts = $this->getTopProducts();
        $topDebtors = $this->getTopDebtors();
    @endphp

    <div class="space-y-6">
        <div>
            {{ $this->form }}
        </div>

        <div class="grid gap-4 lg:grid-cols-5">
            @foreach ($this->getStats() as $stat)
                <div class="rounded-2xl border border-gray-200/60 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ $stat['title'] }}</p>
                    <p class="mt-1 text-2xl font-semibold tracking-tight">{{ $stat['value'] }}</p>
                    <p class="text-xs text-gray-400">{{ $stat['description'] }}</p>
                </div>
            @endforeach
        </div>

        <div class="grid gap-6 lg:grid-cols-3">
            <div
                class="col-span-3 rounded-2xl border border-gray-200/60 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900 lg:col-span-2"
                x-data="lineChart(@js($salesChart))">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <p class="text-sm font-medium text-gray-500">Динамика</p>
                        <h3 class="text-xl font-semibold text-gray-900 dark:text-white">Поступления vs расходы</h3>
                    </div>
                    <span class="text-xs text-gray-400">С {{ $filters['start_date'] ?? '—' }} по
                        {{ $filters['end_date'] ?? '—' }}</span>
                </div>
                <div class="mt-4" wire:ignore>
                    <div x-ref="chart" class="h-64 w-full"></div>
                </div>
            </div>

            <div class="rounded-2xl border border-gray-200/60 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900"
                x-data="pieChart(@js($debtChart))">
                <h3 class="text-xl font-semibold text-gray-900 dark:text-white">Структура долгов</h3>
                <p class="text-sm text-gray-500">Сравнение обязательств клиентов и компании</p>
                <div class="mt-4" wire:ignore>
                    <div x-ref="chart" class="h-64 w-full"></div>
                </div>
            </div>
        </div>

        <div class="grid gap-6 lg:grid-cols-2">
            <div class="rounded-2xl border border-gray-200/60 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500">Товары</p>
                        <h3 class="text-xl font-semibold text-gray-900 dark:text-white">Топ продаж</h3>
                    </div>
                </div>

                @if (count($topProducts))
                    <div class="mt-4 overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-sm dark:divide-gray-800">
                            <thead>
                                <tr class="text-left text-gray-500">
                                    <th class="py-2 font-medium">Товар</th>
                                    <th class="py-2 font-medium">Кол-во</th>
                                    <th class="py-2 text-right font-medium">Выручка</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                @foreach ($topProducts as $product)
                                    <tr class="text-gray-900 dark:text-gray-100">
                                        <td class="py-2 font-medium">{{ $product['name'] }}</td>
                                        <td class="py-2">{{ number_format($product['quantity'], 2, ',', ' ') }}</td>
                                        <td class="py-2 text-right font-semibold">{{ $product['total'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="mt-4 text-sm text-gray-500">Нет продаж за выбранный период.</p>
                @endif
            </div>

            <div class="rounded-2xl border border-gray-200/60 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500">Клиенты</p>
                        <h3 class="text-xl font-semibold text-gray-900 dark:text-white">Топ по долгам</h3>
                    </div>
                </div>

                @if (count($topDebtors))
                    <div class="mt-4 space-y-3">
                        @foreach ($topDebtors as $debtor)
                            <div class="flex items-center justify-between rounded-xl border border-gray-100 px-3 py-2 dark:border-gray-800">
                                <div>
                                    <p class="font-medium text-gray-900 dark:text-white">{{ $debtor['name'] }}</p>
                                    <p class="text-xs text-gray-500">{{ $debtor['phone'] ?: '—' }}</p>
                                </div>
                                <span class="text-sm font-semibold text-orange-500">{{ $debtor['balance'] }}</span>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="mt-4 text-sm text-gray-500">Долгов нет.</p>
                @endif
            </div>
        </div>
    </div>

    @once
        @push('scripts')
            <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
            <script>
                document.addEventListener('alpine:init', () => {
                    Alpine.data('lineChart', (chartData) => ({
                        chart: null,
                        data: chartData,
                        init() {
                            this.render();
                        },
                        render() {
                            if (this.chart) {
                                this.chart.destroy();
                            }

                            const options = {
                                chart: {
                                    type: 'area',
                                    height: 260,
                                    toolbar: { show: false },
                                    fontFamily: 'Inter, sans-serif',
                                },
                                stroke: {
                                    curve: 'smooth',
                                    width: 3,
                                },
                                dataLabels: { enabled: false },
                                colors: ['#22c55e', '#ef4444'],
                                fill: {
                                    type: 'gradient',
                                    gradient: {
                                        shadeIntensity: 1,
                                        opacityFrom: 0.4,
                                        opacityTo: 0.1,
                                        stops: [0, 90, 100],
                                    },
                                },
                                series: this.data.series,
                                xaxis: {
                                    categories: this.data.categories,
                                    labels: { style: { colors: '#9ca3af' } },
                                },
                                yaxis: {
                                    labels: { style: { colors: '#9ca3af' } },
                                },
                                legend: {
                                    position: 'top',
                                    horizontalAlign: 'left',
                                    labels: { colors: '#6b7280' },
                                },
                                tooltip: { theme: 'light' },
                            };

                            this.chart = new ApexCharts(this.$refs.chart, options);
                            this.chart.render();
                        },
                    }));

                    Alpine.data('pieChart', (chartData) => ({
                        chart: null,
                        data: chartData,
                        init() {
                            this.render();
                        },
                        render() {
                            if (this.chart) {
                                this.chart.destroy();
                            }

                            const options = {
                                chart: {
                                    type: 'donut',
                                    height: 260,
                                    fontFamily: 'Inter, sans-serif',
                                },
                                series: this.data.series,
                                labels: this.data.labels,
                                colors: ['#fb923c', '#facc15'],
                                legend: {
                                    position: 'bottom',
                                    labels: { colors: '#6b7280' },
                                },
                                dataLabels: { enabled: true },
                            };

                            this.chart = new ApexCharts(this.$refs.chart, options);
                            this.chart.render();
                        },
                    }));
                });
            </script>
        @endpush
    @endonce
</x-filament-panels::page>
