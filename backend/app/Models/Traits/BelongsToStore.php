<?php

namespace App\Models\Traits;

use App\Models\Scopes\StoreScope;
use Illuminate\Support\Facades\Auth;

trait BelongsToStore
{
    /**
     * O método "boot" de uma trait roda automaticamente 
     * quando o Model é inicializado.
     */
    protected static function bootBelongsToStore()
    {
        // 1. Aplica o filtro de leitura invisível
        static::addGlobalScope(new StoreScope);

        // 2. Preenche o store_id automaticamente na hora de CRIAR (Insert)
        static::creating(function ($model) {
            if (Auth::check() && Auth::user()->store_id && empty($model->store_id)) {
                $model->store_id = Auth::user()->store_id;
            }
        });
    }
}
