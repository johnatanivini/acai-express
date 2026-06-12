<?php

namespace App\Models;

use App\Models\Traits\BelongsToStore;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

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
        'order_hash',
    ];

    /**
     * Casts para garantir que os dados JSON sejam tratados como array no PHP
     */
    protected $casts = [
        'delivery_address' => 'array',
        'total_amount' => 'decimal:2',
        'order_hash' => 'string',
    ];

    protected static function booted()
    {
        static::creating(function ($order) {
            // Gera um hash aleatório seguro e único de 32 caracteres antes de salvar
            $order->order_hash = Str::random(32);
        });
    }

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

    public function extras()
    {
        return $this->hasManyThrough(Extra::class, OrderItem::class, 'order_id', 'id', 'id', 'extra_id');
    }

    // Dentro do seu app/Models/Order.php
    public function products()
    {
        return $this->belongsToMany(Product::class, 'order_items')
            ->withPivot([
                'quantity',
                'price_at_purchase',
                'unit_price',
                'total_price',
                'product_name',
                'extras'
            ])
            ->withTimestamps();
    }
}
