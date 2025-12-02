<?php

namespace App\Observers;

use App\Models\ClientDebtPayment;
use App\Services\DebtService;

class ClientDebtPaymentObserver
{
    public function __construct(private readonly DebtService $debtService)
    {
    }

    public function created(ClientDebtPayment $payment): void
    {
        $this->debtService->applyClientPayment($payment);
    }
}
