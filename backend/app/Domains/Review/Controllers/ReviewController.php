<?php

namespace App\Domains\Review\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Review;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function store(Request $request, $order_hash)
    {
        // 1. Localiza o pedido pelo hash público
        $order = Order::withoutGlobalScopes()->where('order_hash', $order_hash)->firstOrFail();

        // 2. Impede duplicidade (um pedido só pode ser avaliado uma vez)
        $alreadyReviewed = Review::withoutGlobalScopes()->where('order_id', $order->id)->exists();
        if ($alreadyReviewed) {
            return response()->json(['message' => 'Este pedido já foi avaliado.'], 400);
        }

        // 3. Valida as notas (obrigatórias e de 1 a 5)
        $request->validate([
            'rating_quality'   => 'required|integer|between:1,5',
            'rating_delivery'  => 'required|integer|between:1,5',
            'rating_packaging' => 'required|integer|between:1,5',
            'rating_service'   => 'required|integer|between:1,5',
            'rating_value'     => 'required|integer|between:1,5',
            'comment'          => 'nullable|string|max:500',
        ]);

        // 4. MÁGICA DA MÉDIA PONDERADA
        $q = $request->rating_quality;
        $d = $request->rating_delivery;
        $p = $request->rating_packaging;
        $s = $request->rating_service;
        $v = $request->rating_value;

        $finalScore = (($q * 3) + ($d * 2) + ($p * 1) + ($s * 1) + ($v * 1)) / 8;

        // 5. Salva a avaliação atrelada à loja do pedido
        $review = Review::create([
            'store_id'         => $order->store_id,
            'order_id'         => $order->id,
            'rating_quality'   => $q,
            'rating_delivery'  => $d,
            'rating_packaging' => $p,
            'rating_service'   => $s,
            'rating_value'     => $v,
            'final_score'      => $finalScore,
            'comment'          => $request->comment
        ]);

        return response()->json([
            'message' => 'Avaliação enviada com sucesso! Obrigado.',
            'final_score' => round($finalScore, 2)
        ], 201);
    }
}
