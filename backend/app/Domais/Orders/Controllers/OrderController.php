<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    /**
     * Retorna a fila de pedidos da loja (Dashboard)
     */
    public function indexAdmin(Request $request)
    {
        $user = $request->user();

        // 1. Barreira de Segurança: O usuário logado é dono de loja?
        if (!$user->isStoreAdmin()) {
            return response()->json(['message' => 'Acesso negado. Apenas administradores da loja.'], 403);
        }

        // 2. Busca os pedidos APENAS da loja deste administrador
        $orders = Order::with('items') // Traz os itens (Açaí, Extras) junto com o pedido
            ->where('store_id', $user->store_id)
            ->orderBy('created_at', 'desc') // Mais recentes primeiro
            ->get();

        return response()->json($orders);
    }

    /**
     * Atualiza o status do pedido (Preparando, Saiu para Entrega)
     */
    public function updateStatus(Request $request, $id)
    {
        $user = $request->user();

        if (!$user->isStoreAdmin()) {
            return response()->json(['message' => 'Acesso negado.'], 403);
        }

        $request->validate([
            // Status permitidos na aplicação
            'status' => 'required|in:pending,preparing,ready,out_for_delivery,delivered,canceled'
        ]);

        // 3. Blindagem Whitelabel: Busca o pedido, mas garante que ele pertence à loja do admin
        $order = Order::where('store_id', $user->store_id)->find($id);

        if (!$order) {
            return response()->json(['message' => 'Pedido não encontrado ou não pertence a esta loja.'], 404);
        }

        // Atualiza o status
        $order->status = $request->status;
        $order->save();

        return response()->json([
            'message' => 'Status do pedido atualizado com sucesso.',
            'order' => $order
        ]);
    }
}
