<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;

class StoreScope implements Scope
{
    /**
     * Aplica o escopo a todas as queries do Eloquent.
     */
    public function apply(Builder $builder, Model $model)
    {
        // Se o usuário estiver logado e pertencer a uma loja...
        if (Auth::check() && Auth::user()->store_id) {
            // Filtra silenciosamente tudo para a loja dele!
            $builder->where('store_id', Auth::user()->store_id);
        }
    }
}
