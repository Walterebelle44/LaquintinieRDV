<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
    body { font-family: sans-serif; font-size: 12px; color: #0f1c33; margin: 0; padding: 16px; }
    .header { text-align: center; margin-bottom: 12px; }
    .header .badge { display: inline-block; background: #1554D1; color: white; padding: 4px 10px; border-radius: 6px; font-size: 10px; letter-spacing: 1px; }
    h1 { font-size: 16px; margin: 8px 0 2px; }
    .sub { color: #6f8bb8; font-size: 10px; }
    .divider { border-top: 1px dashed #c7d5e8; margin: 14px 0; }
    table.info td { padding: 4px 0; font-size: 11px; }
    table.info td.label { color: #6f8bb8; width: 40%; }
    table.info td.value { font-weight: bold; text-align: right; }
    .statut { text-align: center; margin: 14px 0; }
    .statut span { background: #e7f9f1; color: #067647; padding: 4px 12px; border-radius: 20px; font-size: 11px; font-weight: bold; }
    .qr { text-align: center; margin-top: 16px; }
    .footer { text-align: center; margin-top: 14px; font-size: 9px; color: #9fb3d3; }
</style>
</head>
<body>
    <div class="header">
        <span class="badge">HÔPITAL LAQUINTINIE</span>
        <h1>Ticket de rendez-vous</h1>
        <div class="sub">{{ $ticket->numero }}</div>
    </div>

    <div class="divider"></div>

    <table class="info" width="100%">
        <tr><td class="label">Patient</td><td class="value">{{ $rdv->patient->nom_complet }}</td></tr>
        <tr><td class="label">Médecin</td><td class="value">Dr {{ $rdv->medecin->user->prenom }} {{ $rdv->medecin->user->nom }}</td></tr>
        <tr><td class="label">Spécialité</td><td class="value">{{ $rdv->medecin->specialites->pluck('nom')->join(', ') }}</td></tr>
        <tr><td class="label">Date</td><td class="value">{{ $rdv->date->translatedFormat('d F Y') }}</td></tr>
        <tr><td class="label">Heure</td><td class="value">{{ $rdv->heure_debut }}</td></tr>
        @if($rdv->medecin->salle)
        <tr><td class="label">Salle</td><td class="value">{{ $rdv->medecin->salle }}</td></tr>
        @endif
    </table>

    <div class="statut">
        <span>{{ strtoupper($rdv->statut) }}</span>
    </div>

    <div class="qr">
        {!! $qrSvg !!}
        <div class="sub">Présentez ce QR code à l'accueil</div>
    </div>

    <div class="footer">
        Hôpital Laquintinie de Douala — Boulevard de la Liberté, Akwa<br>
        Document généré automatiquement, valable pour le rendez-vous indiqué ci-dessus.
    </div>
</body>
</html>
