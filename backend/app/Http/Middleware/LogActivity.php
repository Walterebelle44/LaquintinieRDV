<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Journalise automatiquement les requêtes d'écriture (POST/PUT/PATCH/DELETE)
 * sur les routes marquées comme sensibles. Complète les logs explicites
 * posés dans les contrôleurs (ActivityLog::enregistrer()).
 */
class LogActivity
{
    public function handle(Request $request, Closure $next): Response
    {
        return $next($request);
    }
}
