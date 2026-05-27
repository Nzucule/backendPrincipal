<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AdminMiddleware
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

        // Verificar se o usuário é admin
        if ($request->user()->role !== 'admin') {
            // Log para debug (opcional)
            Log::warning('Tentativa de acesso admin negado para usuário: ' . $request->user()->email);
            
            return response()->json([
                'success' => false,
                'message' => 'Acesso negado. Apenas administradores podem acessar esta rota.'
            ], 403);
        }

        // Se for admin, permite o acesso
        return $next($request);
    }
}