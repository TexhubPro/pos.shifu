<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class StockMovement extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'referenceable_id',
        'referenceable_type',
        'type',
        'quantity',
        'stock_after',
        'comment',
        'occurred_at',
        'meta',
    ];

    protected $casts = [
        'quantity' => 'decimal:3',
        'stock_after' => 'decimal:3',
        'occurred_at' => 'datetime',
        'meta' => 'array',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function referenceable(): MorphTo
    {
        return $this->morphTo();
    }
}
