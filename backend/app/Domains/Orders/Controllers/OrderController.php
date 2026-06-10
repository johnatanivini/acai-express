<?php

namespace App\Domains\Orders\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Extra;
use App\Models\Order;
use App\Models\Product;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

    public function store($slug, Request $request)
    {
        // Lógica para criar um novo pedido (pode ser redirecionamento para WhatsApp ou geração de link de pagamento Stripe)    
        // 1. Encontra a loja pelo slug da URL pública
        $store = Store::withoutGlobalScopes()->where('slug', $slug)->firstOrFail();

        // 2. Valida rigidamente a estrutura do carrinho
        $request->validate([
            'customer_name'       => 'required|string|max:255',
            'customer_whatsapp'   => 'required|string|max:20',
            'delivery_address'    => 'required|string',
            'payment_method'      => 'required|in:money,pix,card',
            'items'               => 'required|array|min:1',
            'items.*.product_id'  => 'required|exists:products,id',
            'items.*.quantity'    => 'required|integer|min:1',
            'items.*.extras'      => 'array',
            'items.*.extras.*'    => 'exists:extras,id',
        ]);

        // 3. Inicia a transação com o banco de dados
        return DB::transaction(function () use ($request, $store) {

            // Criamos o pedido principal com o valor zerado (vamos somar os itens abaixo)
            // Como as tabelas não têm as colunas separadas para nome/whatsapp do cliente,
            // agrupamos essas informações no JSON da coluna delivery_address.
            $order = Order::create([
                'store_id'          => $store->id,
                'delivery_address'  => [
                    'customer_name'     => $request->customer_name,
                    'customer_whatsapp' => $request->customer_whatsapp,
                    'address'           => $request->delivery_address,
                ],
                'payment_method'    => $request->payment_method,
                'status'            => 'pending', // Aguardando aprovação na cozinha
                'total_amount'      => 0,
            ]);

            $orderTotal = 0;

            // 4. Varre os itens enviados pelo Nuxt calculando os valores reais do banco
            foreach ($request->items as $itemData) {
                $product = Product::withoutGlobalScopes()->findOrFail($itemData['product_id']);

                $itemSubtotal = $product->price;
                $selectedExtras = [];

                // Se o item tiver adicionais (Nutella, Leite Ninho, etc.)
                if (!empty($itemData['extras'])) {
                    foreach ($itemData['extras'] as $extraId) {
                        $extra = Extra::withoutGlobalScopes()->findOrFail($extraId);
                        $itemSubtotal += $extra->price;
                        $selectedExtras[] = [
                            'id'    => $extra->id,
                            'name'  => $extra->name,
                            'price' => $extra->price,
                        ];
                    }
                }

                // Multiplica pela quantidade de açaís idênticos solicitada
                $itemTotal = $itemSubtotal * $itemData['quantity'];
                $orderTotal += $itemTotal;

                // Cria o item do pedido associando o produto e os extras selecionados no campo JSON.
                $order->items()->create([
                    'product_id'      => $product->id,
                    'product_name'    => $product->name,
                    'unit_price'      => $product->price,
                    'quantity'        => $itemData['quantity'],
                    'selected_extras' => $selectedExtras,
                    'total_price'     => $itemTotal,
                ]);
            }

            // 5. Atualiza o valor total blindado do pedido e salva
            $order->total_amount = $orderTotal;
            $order->save();

            return response()->json([
                'message' => 'Pedido enviado com sucesso para a cozinha!',
                'order_id' => $order->id,
                'total' => $orderTotal
            ], 201);
        });
    }
}
