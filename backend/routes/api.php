<?php

use App\Domais\Auth\Controllers\AuthController;
use App\Domais\Catalog\Controllers\CatalogController;
use App\Domais\Orders\Controllers\OrderController;
use App\Domais\Payments\Controllers\StripeWebhookController;
use App\Domais\Tenant\Controllers\TenantController;
use Illuminate\Support\Facades\Route;
/*
|--------------------------------------------------------------------------
| 🏪 Módulo Tenant & Catálogo (Rotas Públicas)
|--------------------------------------------------------------------------
| O cliente não precisa de login para ver a loja e o cardápio.
*/

Route::prefix('tenant/{slug}')->group(function () {
    // Retorna os dados da loja (Logo, Cores, Endereço, WhatsApp)
    Route::get('/', [TenantController::class, 'show']);

    // Retorna o cardápio completo (Categorias, Produtos, Combos e Extras)
    Route::get('/catalog', [CatalogController::class, 'index']);

    // Cria um novo pedido (Pode redirecionar pro WhatsApp ou gerar link de pagamento Stripe)
    Route::post('/orders', [OrderController::class, 'store']);
});


/*
|--------------------------------------------------------------------------
| 🛒 Módulo de Pedidos (Rotas Mistas)
|--------------------------------------------------------------------------
*/
// Rota pública para o cliente acompanhar o status do pedido pelo link
Route::get('/orders/{order_hash}', [OrderController::class, 'show']);

// Webhook do Stripe (Recebe as confirmações de pagamento em background)
Route::post('/webhooks/stripe', [StripeWebhookController::class, 'handle']);

/*
|--------------------------------------------------------------------------
| 🔒 Módulo de Autenticação (Rotas Públicas de Login)
|--------------------------------------------------------------------------
*/
Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
});

/*
|--------------------------------------------------------------------------
| 🛡️ Módulo Admin do Lojista (Rotas Privadas - Requer Sanctum)
|--------------------------------------------------------------------------
| Aqui o Nuxt enviará o Bearer Token no cabeçalho.
*/
Route::middleware('auth:sanctum')->group(function () {

    // Logout da aplicação
    Route::post('/auth/logout', [AuthController::class, 'logout']);

    // Dashboard do Lojista
    Route::prefix('admin')->group(function () {

        // Retorna a fila de pedidos da loja (Dashboard)
        Route::get('/orders', [OrderController::class, 'indexAdmin']);

        // Atualiza o status do pedido (Preparando, Saiu para Entrega)
        Route::patch('/orders/{id}/status', [OrderController::class, 'updateStatus']);
    });
});
