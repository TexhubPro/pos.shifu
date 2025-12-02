<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Supplier extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'phone',
        'email',
        'address',
        'balance',
        'notes',
    ];

    protected $casts = [
        'balance' => 'decimal:2',
    ];

    /**
     * @return HasMany<Purchase>
     */
    public function purchases(): HasMany
    {
        return $this->hasMany(Purchase::class);
    }

    /**
     * @return HasMany<SupplierDebtPayment>
     */
    public function debtPayments(): HasMany
    {
        return $this->hasMany(SupplierDebtPayment::class);
    }
}
