<?php

namespace App\Services;

use App\Models\CurrencyRate;
use Illuminate\Support\Facades\Cache;

class CurrencyService
{
    public function getActiveRate(string $currency = 'USD'): float
    {
        $cacheKey = sprintf('currency_rate_%s', $currency);

        return Cache::remember($cacheKey, now()->addMinutes(10), function () use ($currency) {
            $rate = CurrencyRate::query()
                ->where('currency_code', $currency)
                ->orderByDesc('is_active')
                ->orderByDesc('effective_at')
                ->value('rate');

            return (float) ($rate ?? config('pos.default_usd_rate', 11.0));
        });
    }

    public function usdToTjs(float|int|string $amount, ?float $rate = null): float
    {
        $rate = $rate ?? $this->getActiveRate();

        return round((float) $amount * $rate, 2);
    }

    public function tjsToUsd(float|int|string $amount, ?float $rate = null): float
    {
        $rate = $rate ?? $this->getActiveRate();

        if ($rate <= 0) {
            return 0.0;
        }

        return round((float) $amount / $rate, 2);
    }

    public function deactivateCachedRates(?string $currency = null): void
    {
        $key = sprintf('currency_rate_%s', $currency ?? 'USD');
        Cache::forget($key);
    }

    public function setActiveRate(float $rate, string $currency = 'USD', ?string $notes = null): CurrencyRate
    {
        CurrencyRate::query()
            ->where('currency_code', $currency)
            ->where('is_active', true)
            ->update(['is_active' => false]);

        $record = CurrencyRate::create([
            'currency_code' => $currency,
            'rate' => $rate,
            'effective_at' => now(),
            'is_active' => true,
            'notes' => $notes,
        ]);

        $this->deactivateCachedRates($currency);

        return $record;
    }
}
