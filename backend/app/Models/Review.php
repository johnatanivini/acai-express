<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\BelongsToStore; // Mantém a segurança Whitelabel

class Review extends Model
{
    use BelongsToStore;

    protected $fillable = [
        'store_id',
        'order_id',
        'comment',
        'rating_quality',
        'rating_delivery',
        'rating_packaging',
        'rating_service',
        'rating_value',
        'final_score'
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
