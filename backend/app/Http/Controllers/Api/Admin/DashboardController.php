<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Medecin;
use App\Models\RendezVous;
use App\Models\Specialite;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    /**
     * Statistiques globales pour le tableau de bord admin
     * (cahier des charges §6 — administration et reporting).
     */
    public function index(Request $request)
    {
        $debutMois = Carbon::now()->startOfMonth();

        $totalUtilisateurs = User::count();
        $totalMedecinsActifs = Medecin::whereHas('user', fn ($q) => $q->where('statut', 'actif'))->count();
        $rdvCeMois = RendezVous::whereDate('date', '>=', $debutMois)->count();

        $totalRdv = RendezVous::count();
        $annules = RendezVous::where('statut', 'annule')->count();
        $tauxAnnulation = $totalRdv > 0 ? round(($annules / $totalRdv) * 100, 1) : 0;

        $repartitionParStatut = RendezVous::selectRaw('statut, count(*) as total')
            ->groupBy('statut')
            ->pluck('total', 'statut');

        $repartitionParSpecialite = Specialite::withCount(['medecins as rdv_count' => function ($q) {
            $q->join('rendez_vous', 'rendez_vous.medecin_id', '=', 'medecins.id');
        }])->get(['id', 'nom']);

        return response()->json([
            'data' => [
                'total_utilisateurs' => $totalUtilisateurs,
                'total_medecins_actifs' => $totalMedecinsActifs,
                'rdv_ce_mois' => $rdvCeMois,
                'taux_annulation' => $tauxAnnulation,
                'repartition_statuts' => $repartitionParStatut,
                'repartition_specialites' => $repartitionParSpecialite,
            ],
        ]);
    }

    /** Export CSV simple des rendez-vous (base pour export PDF/Excel côté frontend ou job dédié). */
    public function exportRendezVous(Request $request)
    {
        $rdvs = RendezVous::with(['patient', 'medecin.user'])
            ->when($request->filled('date_debut'), fn ($q) => $q->whereDate('date', '>=', $request->date('date_debut')))
            ->when($request->filled('date_fin'), fn ($q) => $q->whereDate('date', '<=', $request->date('date_fin')))
            ->get();

        $filename = 'rendez-vous-' . now()->format('Y-m-d') . '.csv';

        $callback = function () use ($rdvs) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Ticket', 'Patient', 'Médecin', 'Date', 'Heure', 'Statut']);
            foreach ($rdvs as $r) {
                fputcsv($handle, [
                    $r->ticket?->numero,
                    $r->patient->nom_complet,
                    'Dr ' . $r->medecin->user->nom,
                    $r->date->format('d/m/Y'),
                    $r->heure_debut,
                    $r->statut,
                ]);
            }
            fclose($handle);
        };

        return response()->stream($callback, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename={$filename}",
        ]);
    }
}
