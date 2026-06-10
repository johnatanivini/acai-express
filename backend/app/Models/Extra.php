<?php

namespace App\Models;

use App\Models\Traits\BelongsToStore;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Extra extends Model
{
    use HasFactory, BelongsToStore;

    protected $fillable = [
        'store_id',
        'name',
        'price',
        'is_stock_managed',
        'stock_quantity',
        'is_active',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_stock_managed' => 'boolean',
        'stock_quantity' => 'decimal:3',
        'is_active' => 'boolean',
    ];

    /**
     * Relacionamento: Um extra pertence a uma Loja.
     */
    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    /**
     * Relacionamento: Um extra pode estar em vários produtos.
     */
    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_extra')
            ->withTimestamps();
    }
}
