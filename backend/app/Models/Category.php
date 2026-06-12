<?php

namespace App\Models;

use App\Models\Traits\BelongsToStore;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory, BelongsToStore;

    protected $fillable = ['store_id', 'name', 'sort_order'];

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function extras()
    {
        return $this->hasMany(Extra::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }
}
