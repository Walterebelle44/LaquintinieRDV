<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\RendezVous;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class TicketController extends Controller
{
    /**
     * Génère (ou régénère) et retourne le PDF imprimable du ticket de rendez-vous.
     * Accessible au patient propriétaire du RDV ou à un admin/secrétaire.
     */
    public function telecharger(Request $request, RendezVous $rendezVous)
    {
        $user = $request->user();
        $estProprietaire = $rendezVous->patient_id === $user->id;
        $estPersonnel = $user->hasAnyRole(['admin', 'secretaire']);

        if (! $estProprietaire && ! $estPersonnel) {
            return response()->json(['message' => 'Non autorisé.'], 403);
        }

        if (! $rendezVous->ticket) {
            return response()->json(['message' => 'Aucun ticket disponible : le rendez-vous n\'est pas encore confirmé.'], 404);
        }

        $rendezVous->load(['medecin.user', 'medecin.specialites', 'patient', 'ticket']);

        $qrSvg = QrCode::size(160)->generate($rendezVous->uuid);

        $pdf = Pdf::loadView('tickets.pdf', [
            'rdv' => $rendezVous,
            'ticket' => $rendezVous->ticket,
            'qrSvg' => $qrSvg,
        ])->setPaper([0, 0, 320, 520]); // format ticket allongé

        return $pdf->download("ticket-{$rendezVous->ticket->numero}.pdf");
    }
}
