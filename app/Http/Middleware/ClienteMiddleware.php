<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ClienteMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Verificar se o usuário está autenticado
        if (!$request->user()) {
            return response()->json([
                'success' => false,
                'message' => 'Usuário não autenticado.'
            ], 401);
        }

        // Verificar se o usuário é cliente
        if ($request->user()->role !== 'cliente') {
            return response()->json([
                'success' => false,
                'message' => 'Acesso negado. Apenas clientes podem acessar esta rota.'
            ], 403);
        }

        // Se for cliente, permite o acesso
        return $next($request);
    }
}