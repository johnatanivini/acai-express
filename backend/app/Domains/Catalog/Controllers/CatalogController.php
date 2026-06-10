<?php

namespace App\Domains\Catalog\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Store;
use Illuminate\Http\Request;

class CatalogController extends Controller
{
    /**
     * Retorna a vitrine pública de uma loja específica (Cardápio Online)
     */
    public function index($slug)
    {
        // 1. Encontra a loja pela URL (slug) e garante que está ativa
        $store = Store::where('slug', $slug)->where('is_active', true)->first();

        if (!$store) {
            return response()->json(['message' => 'Loja não encontrada ou indisponível.'], 404);
        }

        // 2. Evitando o problema de N+1 queries)
        // Busca as categorias desta loja e já "puxa" os produtos ativos e os seus extras num único comando SQL.
        $categories = $store->categories()
            ->orderBy('sort_order')
            ->with(['products' => function ($query) {
                $query->where('is_active', true)
                    ->with(['extras' => function ($queryExtra) {
                        $queryExtra->where('is_active', true);
                    }]);
            }])
            ->get();

        // 3. Monta a resposta estruturada para o Nuxt consumir
        return response()->json([
            'store' => [
                'id' => $store->id,
                'name' => $store->name,
                'logo_url' => $store->logo_url,
                'primary_color' => $store->primary_color,
                'whatsapp_number' => $store->whatsapp_number,
            ],
            'menu' => $categories
        ]);
    }
}
