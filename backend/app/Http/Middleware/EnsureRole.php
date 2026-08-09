<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware générique de contrôle d'accès par rôle.
 * Utilisation dans les routes : ->middleware('role:admin') ou ->middleware('role:admin,medecin')
 */
class EnsureRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user || ! $user->hasAnyRole($roles)) {
            return response()->json([
                'message' => "Accès refusé : rôle insuffisant pour cette action.",
            ], 403);
        }

        if (! $user->estActif()) {
            return response()->json([
                'message' => 'Votre compte est bloqué. Contactez l\'administration.',
            ], 403);
        }

        return $next($request);
    }
}
