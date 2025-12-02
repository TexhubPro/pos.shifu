<?php

namespace App\Services;

use App\Models\Client;
use App\Models\ClientDebtPayment;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\Supplier;
use App\Models\SupplierDebtPayment;
use Illuminate\Support\Facades\DB;

class DebtService
{
    public function refreshClientBalance(Client $client): void
    {
        $client->loadMissing('sales', 'debtPayments');

        $balance = $client->sales()->sum('balance');
        $lifetime = $client->sales()->sum('total');

        $client->forceFill([
            'balance' => $balance,
            'lifetime_spend' => $lifetime,
        ])->save();
    }

    public function refreshSupplierBalance(Supplier $supplier): void
    {
        $supplier->loadMissing('purchases', 'debtPayments');

        $balance = $supplier->purchases()->sum('balance');

        $supplier->forceFill([
            'balance' => $balance,
        ])->save();
    }

    public function applyClientPayment(ClientDebtPayment $payment): void
    {
        DB::transaction(function () use ($payment) {
            if ($payment->sale) {
                $sale = Sale::query()->lockForUpdate()->find($payment->sale_id);

                if ($sale) {
                    $sale->paid_amount = round($sale->paid_amount + $payment->amount, 2);
                    $sale->balance = max(round($sale->total - $sale->paid_amount, 2), 0);
                    $sale->on_credit = $sale->balance > 0;
                    if ($sale->balance <= 0) {
                        $sale->status = 'completed';
                    }
                    $sale->save();
                }
            }

            $client = Client::query()->lockForUpdate()->find($payment->client_id);

            if ($client) {
                if ($payment->sale_id) {
                    $this->refreshClientBalance($client);
                } else {
                    $client->forceFill([
                        'balance' => max(round($client->balance - $payment->amount, 2), 0),
                    ])->save();
                }
            }
        });
    }

    public function applySupplierPayment(SupplierDebtPayment $payment): void
    {
        DB::transaction(function () use ($payment) {
            if ($payment->purchase) {
                $purchase = Purchase::query()->lockForUpdate()->find($payment->purchase_id);

                if ($purchase) {
                    $purchase->paid_amount = round($purchase->paid_amount + $payment->amount, 2);
                    $purchase->balance = max(round($purchase->total - $purchase->paid_amount, 2), 0);
                    $purchase->payment_status = $this->resolvePaymentStatus($purchase->balance, $purchase->total);
                    $purchase->is_credit = $purchase->balance > 0;
                    $purchase->save();
                }
            }

            $supplier = Supplier::query()->lockForUpdate()->find($payment->supplier_id);

            if ($supplier) {
                if ($payment->purchase_id) {
                    $this->refreshSupplierBalance($supplier);
                } else {
                    $supplier->forceFill([
                        'balance' => max(round($supplier->balance - $payment->amount, 2), 0),
                    ])->save();
                }
            }
        });
    }

    private function resolvePaymentStatus(float $balance, float $total): string
    {
        if ($balance <= 0) {
            return 'paid';
        }

        if ($balance >= $total) {
            return 'unpaid';
        }

        return 'partial';
    }
}
