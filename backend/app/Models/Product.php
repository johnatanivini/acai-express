<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'name',
        'description',
        'price',
        'is_stock_managed',
        'stock_quantity',
        'image_url',
        'is_active',
    ];

    protected $casts = [
        'price' => 'decimal:2',            // Ex: 15.90
        'is_stock_managed' => 'boolean',   // true ou false
        'stock_quantity' => 'decimal:3',   // Ex: 1.500 (1 quilo e meio)
        'is_active' => 'boolean',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function extras()
    {
        // Usamos 'product_extra' como a tabela pivô que conecta Produtos a Extras
        return $this->belongsToMany(Extra::class, 'product_extra')
            ->withTimestamps(); // Boas práticas para rastrear quando a relação foi criada
    }
}
