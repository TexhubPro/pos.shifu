<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    use HasFactory;

    protected $fillable = [
        'category',
        'type',
        'amount',
        'spent_at',
        'payment_method',
        'reference',
        'delivery_products',
        'comment',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'spent_at' => 'date',
        'delivery_products' => 'array',
    ];
}
