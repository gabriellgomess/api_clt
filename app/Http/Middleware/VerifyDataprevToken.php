<?php

namespace App\Http\Middleware;

use App\Models\ClientToken;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class VerifyDataprevToken
{
    /**
     * Handle an incoming request.
     *
     * Verifica se o token Bearer enviado pelo cliente existe na tabela
     * dataprev_client_tokens e está ativo. Os tokens são cacheados por
     * 5 minutos para evitar consultas ao banco a cada requisição.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();

        // Carrega do cache ou do banco: ['valor_do_token' => 'alias']
        $validTokens = Cache::remember('dataprev_client_tokens_active', 300, function () {
            return ClientToken::where('ativo', true)
                ->pluck('alias', 'token')
                ->toArray();
        });

        $alias = $token ? ($validTokens[$token] ?? false) : false;

        if ($alias === false) {
            return response()->json([
                'success' => false,
                'message' => 'Token de autenticação inválido ou não fornecido.',
                'error'   => 'Unauthenticated',
            ], 401);
        }

        // Injeta o alias do sistema no request para uso posterior (ex: controller)
        $request->attributes->set('dataprev_client', $alias);

        return $next($request);
    }
}
