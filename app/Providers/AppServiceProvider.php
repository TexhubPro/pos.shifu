<?php

namespace App\Providers;

use App\Models\ClientDebtPayment;
use App\Models\SupplierDebtPayment;
use App\Observers\ClientDebtPaymentObserver;
use App\Observers\SupplierDebtPaymentObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        ClientDebtPayment::observe(ClientDebtPaymentObserver::class);
        SupplierDebtPayment::observe(SupplierDebtPaymentObserver::class);
    }
}
