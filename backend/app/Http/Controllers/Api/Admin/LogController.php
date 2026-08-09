<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class LogController extends Controller
{
    /**
     * Journal des logs — traçabilité complète de la plateforme, réservé admin
     * (connexions, actions sensibles, exports, alertes de sécurité).
     */
    public function index(Request $request)
    {
        $query = ActivityLog::query()->with('user')->latest();

        if ($request->filled('type')) {
            $query->where('type', $request->string('type'));
        }

        if ($request->filled('q')) {
            $term = '%' . $request->string('q') . '%';
            $query->where(fn ($q) => $q->where('action', 'like', $term)->orWhere('auteur_libelle', 'like', $term));
        }

        if ($request->filled('date_debut')) {
            $query->whereDate('created_at', '>=', $request->date('date_debut'));
        }
        if ($request->filled('date_fin')) {
            $query->whereDate('created_at', '<=', $request->date('date_fin'));
        }

        return response()->json($query->paginate($request->integer('per_page', 25)));
    }
}
