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

        if (!$user->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Tu cuenta está desactivada. Contacta al administrador.',
            ], 403);
        }

        // Registrar la consulta
        \App\Models\ApiLog::create([
            'user_id'       => $user->id,
            'method'        => $request->method(),
            'endpoint'      => $request->path(),
            'ip'            => $request->ip(),
            'user_agent'    => $request->userAgent(),
            'response_code' => 200,
        ]);

        // Pasar el usuario autenticado al request
        $request->merge(['_api_user' => $user]);

        return $next($request);
    }
}
