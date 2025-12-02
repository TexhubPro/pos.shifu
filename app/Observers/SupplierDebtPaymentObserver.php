<?php

namespace App\Observers;

use App\Models\SupplierDebtPayment;
use App\Services\DebtService;

class SupplierDebtPaymentObserver
{
    public function __construct(private readonly DebtService $debtService)
    {
    }

    public function created(SupplierDebtPayment $payment): void
    {
        $this->debtService->applySupplierPayment($payment);
    }
}
