<?php

namespace App\Filament\Pages;

use Carbon\CarbonPeriod;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Actions\Action;
use Filament\Schemas\Schema;
use App\Exports\ReportExport;
use Illuminate\Support\Carbon;
use Filament\Actions\ActionGroup;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Section;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Components\DatePicker;
use Illuminate\Contracts\Support\Htmlable;
use Filament\Forms\Concerns\InteractsWithForms;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class Dashboard extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $title = 'Аналитика и отчёты';

    protected static ?string $navigationLabel = 'Дашборд';

    protected static \UnitEnum|string|null $navigationGroup = 'Аналитика';

    protected static ?string $slug = 'dashboard';

    protected string $view = 'filament.pages.dashboard';

    public array $filters = [];

    public function mount(): void
    {
        $this->form->fill($this->defaultFilters());
    }

    protected function defaultFilters(): array
    {
        $now = now();

        return [
            'preset' => 'this_month',
            'start_date' => $now->copy()->startOfMonth()->toDateString(),
            'end_date' => $now->copy()->endOfMonth()->toDateString(),
        ];
    }

    public function form(Schema $form): Schema
    {
        return $form
            ->schema([
                Section::make('Фильтры периода')
                    ->description('Выберите предустановку или задайте собственный диапазон дат.')
                    ->schema([
                        Select::make('preset')
                            ->label('Быстрый выбор')
                            ->native(false)
                            ->options([
                                'today' => 'Сегодня',
                                'this_week' => 'Текущая неделя',
                                'this_month' => 'Текущий месяц',
                                'custom' => 'Произвольный диапазон',
                            ])
                            ->default('this_month')
                            ->live()
                            ->afterStateUpdated(fn(?string $state, callable $set) => $this->applyPreset($state, $set)),
                        DatePicker::make('start_date')
                            ->label('Начало периода')
                            ->native(false)
                            ->displayFormat('d.m.Y')
                            ->closeOnDateSelection()
                            ->live()
                            ->afterStateUpdated(fn(?string $state, callable $set, callable $get) => $this->handleDateChange('start_date', $state, $get, $set)),
                        DatePicker::make('end_date')
                            ->label('Конец периода')
                            ->native(false)
                            ->displayFormat('d.m.Y')
                            ->closeOnDateSelection()
                            ->live()
                            ->afterStateUpdated(fn(?string $state, callable $set, callable $get) => $this->handleDateChange('end_date', $state, $get, $set)),
                    ])
                    ->columns(3),
            ])
            ->statePath('filters');
    }

    protected function handleDateChange(string $field, ?string $value, callable $get, callable $set): void
    {
        if (! $value) {
            return;
        }

        $otherField = $field === 'start_date' ? 'end_date' : 'start_date';
        $otherValue = $get($otherField);

        if ($otherValue) {
            $start = $field === 'start_date' ? $value : $otherValue;
            $end = $field === 'end_date' ? $value : $otherValue;

            if (Carbon::parse($start)->gt(Carbon::parse($end))) {
                $set($otherField, $value);
            }
        }

        if ($get('preset') !== 'custom') {
            $set('preset', 'custom');
        }
    }

    protected function applyPreset(?string $preset, callable $set): void
    {
        $now = now();

        $range = match ($preset) {
            'today' => [$now->copy()->startOfDay(), $now->copy()->endOfDay()],
            'this_week' => [$now->copy()->startOfWeek(), $now->copy()->endOfWeek()],
            'this_month', null => [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()],
            default => null,
        };

        if (! $range) {
            return;
        }

        [$start, $end] = $range;

        $set('start_date', $start->toDateString());
        $set('end_date', $end->toDateString());
    }

    protected function getHeaderActions(): array
    {
        return [
            ActionGroup::make([
                Action::make('export-sales')
                    ->label('Продажи (Excel)')
                    ->icon('heroicon-o-receipt-percent')
                    ->color('gray')
                    ->action(fn() => $this->exportSalesReport()),
                Action::make('export-debts')
                    ->label('Долги клиентов (Excel)')
                    ->icon('heroicon-o-user-group')
                    ->color('gray')
                    ->action(fn() => $this->exportClientDebtReport()),
                Action::make('export-expenses')
                    ->label('Расходы (Excel)')
                    ->icon('heroicon-o-banknotes')
                    ->color('gray')
                    ->action(fn() => $this->exportExpensesReport()),
            ])
                ->label('Экспорт')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('gray'),
        ];
    }

    public function getHeading(): string|Htmlable
    {
        return 'Аналитика и отчёты';
    }

    public function getStats(): array
    {
        [$start, $end] = $this->getDateRange();

        $sales = DB::table('sales')
            ->selectRaw('
                COALESCE(SUM(total), 0) as total,
                COALESCE(SUM(paid_amount), 0) as paid,
                COALESCE(SUM(balance), 0) as debt
            ')
            ->whereBetween('sold_at', [$start, $end])
            ->first();

        $expenses = DB::table('expenses')
            ->selectRaw('COALESCE(SUM(amount), 0) as total')
            ->whereBetween('spent_at', [$start->toDateString(), $end->toDateString()])
            ->first();

        $clientDebt = DB::table('clients')->sum('balance');
        $supplierDebt = DB::table('suppliers')->sum('balance');

        $salesPaid = (float) ($sales->paid ?? 0);
        $expensesTotal = (float) ($expenses->total ?? 0);

        return [
            [
                'title' => 'Оплаченные продажи',
                'value' => $this->formatCurrency($salesPaid),
                'description' => 'Сколько денег реально получено за период',
                'color' => 'success',
            ],
            [
                'title' => 'Расходы',
                'value' => $this->formatCurrency($expensesTotal),
                'description' => 'Все подтверждённые траты за выбранный период',
                'color' => 'danger',
            ],
            [
                'title' => 'Чистая прибыль',
                'value' => $this->formatCurrency($salesPaid - $expensesTotal),
                'description' => 'Доход минус расходы с учётом оплат',
                'color' => 'primary',
            ],
            [
                'title' => 'Долги клиентов',
                'value' => $this->formatCurrency($clientDebt),
                'description' => 'Сумма, которую нам должны клиенты',
                'color' => 'warning',
            ],
            [
                'title' => 'Долги поставщикам',
                'value' => $this->formatCurrency($supplierDebt),
                'description' => 'Сумма, которую мы должны поставщикам',
                'color' => 'warning',
            ],
        ];
    }

    protected function getDateRange(): array
    {
        $start = Carbon::parse($this->filters['start_date'] ?? now()->startOfMonth());
        $end = Carbon::parse($this->filters['end_date'] ?? now());

        return [$start->startOfDay(), $end->endOfDay()];
    }

    protected function formatCurrency(float $value): string
    {
        return number_format($value, 2, '.', ' ') . ' TJS';
    }

    public function getSalesVsExpensesChart(): array
    {
        [$start, $end] = $this->getDateRange();

        $salesByDate = DB::table('sales')
            ->selectRaw('DATE(sold_at) as day, COALESCE(SUM(paid_amount), 0) as paid_total')
            ->whereBetween('sold_at', [$start, $end])
            ->groupBy('day')
            ->pluck('paid_total', 'day');

        $expensesByDate = DB::table('expenses')
            ->selectRaw('DATE(spent_at) as day, COALESCE(SUM(amount), 0) as expense_total')
            ->whereBetween('spent_at', [$start->toDateString(), $end->toDateString()])
            ->groupBy('day')
            ->pluck('expense_total', 'day');

        $labels = [];
        $sales = [];
        $expenses = [];

        foreach (CarbonPeriod::create($start, $end) as $date) {
            $day = $date->toDateString();
            $labels[] = $date->isoFormat('DD.MM');
            $sales[] = (float) ($salesByDate[$day] ?? 0);
            $expenses[] = (float) ($expensesByDate[$day] ?? 0);
        }

        return [
            'categories' => $labels,
            'series' => [
                [
                    'name' => 'Поступления',
                    'data' => $sales,
                ],
                [
                    'name' => 'Расходы',
                    'data' => $expenses,
                ],
            ],
        ];
    }

    public function getDebtBreakdown(): array
    {
        $clientDebt = (float) DB::table('clients')->sum('balance');
        $supplierDebt = (float) DB::table('suppliers')->sum('balance');

        return [
            'labels' => ['Долги клиентов', 'Долги поставщикам'],
            'series' => [$clientDebt, $supplierDebt],
        ];
    }

    public function getTopProducts(): array
    {
        [$start, $end] = $this->getDateRange();

        return DB::table('sale_items as si')
            ->join('sales as s', 's.id', '=', 'si.sale_id')
            ->join('products as p', 'p.id', '=', 'si.product_id')
            ->selectRaw('p.id, p.name as product_name, COALESCE(SUM(si.quantity), 0) as total_qty, COALESCE(SUM(si.total_price), 0) as total_sum')
            ->whereBetween('s.sold_at', [$start, $end])
            ->groupBy('p.id', 'p.name')
            ->orderByDesc('total_sum')
            ->limit(5)
            ->get()
            ->map(fn($row) => [
                'name' => $row->product_name,
                'quantity' => (float) $row->total_qty,
                'total' => $this->formatCurrency((float) $row->total_sum),
            ])
            ->all();
    }

    public function getTopDebtors(): array
    {
        return DB::table('clients')
            ->select('name', 'phone', 'balance')
            ->where('balance', '>', 0)
            ->orderByDesc('balance')
            ->limit(5)
            ->get()
            ->map(fn($row) => [
                'name' => $row->name,
                'phone' => $row->phone,
                'balance' => $this->formatCurrency((float) $row->balance),
            ])
            ->all();
    }

    protected function exportSalesReport(): BinaryFileResponse
    {
        [$start, $end] = $this->getDateRange();

        $rows = DB::table('sales as s')
            ->leftJoin('clients as c', 'c.id', '=', 's.client_id')
            ->select('s.sold_at', 's.reference', 'c.name as client_name', 's.total', 's.paid_amount', 's.balance')
            ->whereBetween('s.sold_at', [$start, $end])
            ->orderByDesc('s.sold_at')
            ->get()
            ->map(fn($row) => [
                Carbon::parse($row->sold_at)->format('d.m.Y H:i'),
                $row->reference ?? '-',
                $row->client_name ?? 'Не указан',
                (float) $row->total,
                (float) $row->paid_amount,
                (float) $row->balance,
            ])
            ->toArray();

        $meta = [
            ['Начало периода', $start->format('d.m.Y H:i')],
            ['Конец периода', $end->format('d.m.Y H:i')],
        ];

        return $this->downloadReport(
            'sales-report-' . now()->format('Y-m-d-His') . '.xlsx',
            ['Дата продажи', 'Номер', 'Клиент', 'Сумма продажи', 'Оплачено', 'Долг'],
            $rows,
            $meta,
        );
    }

    protected function exportClientDebtReport(): BinaryFileResponse
    {
        $rows = DB::table('clients')
            ->select('name', 'phone', 'balance', 'updated_at')
            ->where('balance', '>', 0)
            ->orderByDesc('balance')
            ->get()
            ->map(fn($row) => [
                $row->name,
                $row->phone ?: '-',
                (float) $row->balance,
                $row->updated_at ? Carbon::parse($row->updated_at)->format('d.m.Y H:i') : '-',
            ])
            ->toArray();

        $meta = [
            ['Дата формирования', now()->format('d.m.Y H:i')],
        ];

        return $this->downloadReport(
            'client-debts-' . now()->format('Y-m-d-His') . '.xlsx',
            ['Клиент', 'Телефон', 'Актуальный долг', 'Дата обновления'],
            $rows,
            $meta,
        );
    }

    protected function exportExpensesReport(): BinaryFileResponse
    {
        [$start, $end] = $this->getDateRange();

        $rows = DB::table('expenses')
            ->select('spent_at', 'category', 'type', 'payment_method', 'amount', 'comment')
            ->whereBetween('spent_at', [$start->toDateString(), $end->toDateString()])
            ->orderByDesc('spent_at')
            ->get()
            ->map(fn($row) => [
                Carbon::parse($row->spent_at)->format('d.m.Y'),
                $row->category,
                $row->type ?? '-',
                $row->payment_method ?? '-',
                (float) $row->amount,
                $row->comment ?? '-',
            ])
            ->toArray();

        $meta = [
            ['Начало периода', $start->format('d.m.Y')],
            ['Конец периода', $end->format('d.m.Y')],
        ];

        return $this->downloadReport(
            'expenses-' . now()->format('Y-m-d-His') . '.xlsx',
            ['Дата', 'Категория', 'Тип', 'Способ оплаты', 'Сумма', 'Комментарий'],
            $rows,
            $meta,
        );
    }

    protected function downloadReport(string $filename, array $headings, array $rows, array $metaRows = []): BinaryFileResponse
    {
        return Excel::download(new ReportExport($headings, $rows, $metaRows), $filename);
    }
}
