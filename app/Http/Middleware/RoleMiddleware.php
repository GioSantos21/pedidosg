<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string $role): Response
    {
        // Verifica si el usuario está autenticado
        if (! $request->user()) {
            // Redirige al login si no está autenticado
            return redirect()->route('login');
        }

        // Verifica si el rol del usuario coincide con el rol requerido
        $roles = explode('|', $role); // Permite múltiples roles separados por '|'

        if (! in_array($request->user()->role, $roles)) {
            // Redirige o muestra un error si el rol no coincide
            abort(403, 'Acceso no autorizado. Tu rol no tiene permiso para esta acción.');
        }

        return $next($request);
    }
}
