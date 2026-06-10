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
                'debug_SaaS' => true,
                'esperado_no_laravel' => $expectedSecret,
                'recebido_do_nuxt' => $providedSecret
            ], 418); // Usamos o status 418 (I'm a teapot) só para sabermos que caiu no nosso debug
            return response()->json([
                'message' => 'Acesso negado. Requisição não originada da aplicação oficial.'
            ], 403);
        }

        // Se estiver tudo certo, deixa a requisição passar para o Controller
        return $next($request);
    }
}
