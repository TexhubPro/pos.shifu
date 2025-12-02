<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Client extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'phone',
        'email',
        'address',
        'balance',
        'lifetime_spend',
        'notes',
    ];

    protected $casts = [
        'balance' => 'decimal:2',
        'lifetime_spend' => 'decimal:2',
    ];

    /**
     * @return HasMany<Sale>
     */
    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    /**
     * @return HasMany<ClientDebtPayment>
     */
    public function debtPayments(): HasMany
    {
        return $this->hasMany(ClientDebtPayment::class);
    }
}
