<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiKeyMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken() ?? $request->query('api_token');

        if (!$token) {
            return response()->json([
                'success' => false,
                'message' => 'API token requerido. Envíalo como Bearer token o parámetro api_token.',
            ], 401);
        }

        $user = \App\Models\User::where('api_token', hash('sha256', $token))->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'API token inválido.',
            ], 401);
        }

        return $next($request);
    }
}
