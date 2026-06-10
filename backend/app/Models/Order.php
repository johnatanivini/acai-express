<?php

namespace App\Models;

use App\Models\Traits\BelongsToStore;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory, BelongsToStore;

    protected $fillable = [
        'store_id',
        'user_id',
        'status',
        'total_amount',
        'payment_method',
        'delivery_address',
        'stripe_payment_intent',
    ];

    /**
     * Casts para garantir que os dados JSON sejam tratados como array no PHP
     */
    protected $casts = [
        'delivery_address' => 'array',
        'total_amount' => 'decimal:2',
    ];

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Os itens individuais do pedido (ex: 1 Açaí Grande + 1 Adicional Nutella)
     */
    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }
}
