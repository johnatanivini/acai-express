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
            'items.*.size_name'   => 'nullable|string', // <-- Nova linha
            'items.*.size_price'  => 'nullable|numeric', // <-- Nova linha
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
            // Dentro do seu foreach de itens, mude a linha do $itemSubtotal:
            foreach ($request->items as $itemData) {
                $product = Product::withoutGlobalScopes()->findOrFail($itemData['product_id']);

                // REGRA COMPENSATÓRIA: Se o front enviou um size_price válido, usa ele. 
                // Caso contrário, usa o preço base do produto vindo do banco.
                $basePrice = isset($itemData['size_price']) && $itemData['size_price'] > 0
                    ? (float) $itemData['size_price']
                    : (float) $product->price;

                $itemSubtotal = $basePrice;
                $extrasSnapshot = [];

                // O restante do loop de extras e o attach continuam exatamente iguais...
                if (!empty($itemData['extras'])) {
                    foreach ($itemData['extras'] as $extraId) {
                        $extra = Extra::withoutGlobalScopes()->findOrFail($extraId);
                        $itemSubtotal += $extra->price;
                        $extrasSnapshot[] = ['id' => $extra->id, 'name' => $extra->name, 'price' => $extra->price];
                    }
                }

                $itemTotal = $itemSubtotal * $itemData['quantity'];
                $orderTotal += $itemTotal;

                $order->products()->attach($product->id, [
                    'quantity'          => $itemData['quantity'],
                    'price_at_purchase' => $basePrice,
                    'unit_price'        => $basePrice,
                    'total_price'       => $itemTotal,
                    'product_name'      => $product->name,
                    'extras'            => json_encode($extrasSnapshot)
                ]);
            }

            // 5. Atualiza o valor total blindado do pedido e salva
            $order->total_amount = $orderTotal;
            $order->save();

            return response()->json([
                'message' => 'Pedido enviado com sucesso para a cozinha!',
                'order_id' => $order->id,
                'order_hash' => $order->order_hash,
                'total' => $orderTotal
            ], 201);
        });
    }

    /**
     * Rota pública de acompanhamento por Hash
     */
    public function show($order_hash)
    {
        // Buscamos o pedido pelo hash carregando junto os dados da loja (para sabermos o WhatsApp do lojista)
        $order = Order::withoutGlobalScopes()
            ->with(['store' => function ($query) {
                $query->withoutGlobalScopes();
            }])
            ->where('order_hash', $order_hash)
            ->first();

        if (!$order) {
            return response()->json(['message' => 'Pedido não localizado.'], 404);
        }

        return response()->json([
            'id'            => $order->id, // ID interno se necessário, ou oculte
            'order_hash'    => $order->order_hash,
            'customer_name' => $order->customer_name,
            'status'        => $order->status,
            'total'         => (float) $order->total_amount,
            'payment_method' => $order->payment_method,
            'address'       => $order->delivery_address,
            'store'         => [
                'name'            => $order->store->name,
                'whatsapp_number' => $order->store->whatsapp_number,
                'slug'            => $order->store->slug,
            ],
            'updated_at'    => $order->updated_at->toIso8601String()
        ]);
    }
}
