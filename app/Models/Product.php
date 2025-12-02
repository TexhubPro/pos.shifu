<?php

namespace App\Models;

use App\Services\CurrencyService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'brand_id',
        'category_id',
        'name',
        'sku',
        'barcode',
        'cost_price',
        'sale_price',
        'wholesale_price',
        'stock',
        'low_stock_threshold',
        'description',
        'images',
        'is_active',
    ];

    protected $casts = [
        'cost_price' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'wholesale_price' => 'decimal:2',
        'stock' => 'decimal:3',
        'images' => 'array',
        'is_active' => 'boolean',
    ];

    protected $appends = [
        'cost_price_usd',
        'sale_price_usd',
    ];

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * @return HasMany<SaleItem>
     */
    public function saleItems(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    /**
     * @return HasMany<PurchaseItem>
     */
    public function purchaseItems(): HasMany
    {
        return $this->hasMany(PurchaseItem::class);
    }

    /**
     * @return HasMany<StockMovement>
     */
    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function scopeAvailable($query)
    {
        return $query->where('is_active', true)->where('stock', '>', 0);
    }

    public function getCostPriceUsdAttribute(): float
    {
        return app(CurrencyService::class)->tjsToUsd($this->cost_price);
    }

    public function getSalePriceUsdAttribute(): float
    {
        return app(CurrencyService::class)->tjsToUsd($this->sale_price);
    }
}
