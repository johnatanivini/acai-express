<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyFrontendSecret
{
    /**
     * Lida com a requisição de entrada.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Pega a chave secreta definida no seu .env
        $expectedSecret = config('app.frontend_secret');

        // Pega a chave que veio na requisição (enviada pelo Nuxt)
        $providedSecret = $request->header('X-Frontend-Secret');

        // Se a chave não existir ou for diferente, bloqueia com erro 403 (Forbidden)
        if (! $providedSecret || $providedSecret !== $expectedSecret) {
            return response()->json([
                'message' => 'Acesso negado. Requisição não originada da aplicação oficial.'
            ], 403);
        }

        // Se estiver tudo certo, deixa a requisição passar para o Controller
        return $next($request);
    }
}
