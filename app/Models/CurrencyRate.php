<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;

class CurrencyRate extends Model
{
    use HasFactory;

    protected $fillable = [
        'currency_code',
        'rate',
        'effective_at',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'rate' => 'decimal:4',
        'effective_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderByDesc('effective_at');
    }

    protected static function booted(): void
    {
        static::saving(function (CurrencyRate $rate) {
            if ($rate->is_active) {
                static::query()
                    ->where('currency_code', $rate->currency_code)
                    ->whereKeyNot($rate->getKey())
                    ->update(['is_active' => false]);
            }
        });

        static::saved(function (CurrencyRate $rate) {
            App::make(\App\Services\CurrencyService::class)->deactivateCachedRates($rate->currency_code);
        });
    }
}
