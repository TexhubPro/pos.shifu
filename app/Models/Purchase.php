<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Purchase extends Model
{
    use HasFactory;

    protected $fillable = [
        'supplier_id',
        'user_id',
        'reference',
        'purchased_at',
        'exchange_rate',
        'subtotal',
        'discount_amount',
        'shipping_cost',
        'total',
        'paid_amount',
        'balance',
        'payment_status',
        'payment_method',
        'due_at',
        'is_credit',
        'notes',
        'meta',
    ];

    protected $casts = [
        'purchased_at' => 'datetime',
        'due_at' => 'date',
        'exchange_rate' => 'decimal:4',
        'subtotal' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'shipping_cost' => 'decimal:2',
        'total' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'balance' => 'decimal:2',
        'is_credit' => 'boolean',
        'meta' => 'array',
    ];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return HasMany<PurchaseItem>
     */
    public function items(): HasMany
    {
        return $this->hasMany(PurchaseItem::class);
    }

    /**
     * @return HasMany<SupplierDebtPayment>
     */
    public function payments(): HasMany
    {
        return $this->hasMany(SupplierDebtPayment::class);
    }
}
