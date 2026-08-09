<?php

// Règles métier configurables — cahier des charges §6 (Règles de gestion)
return [
    'delai_annulation_heures' => env('RDV_DELAI_ANNULATION_HEURES', 24),
    'delai_reponse_medecin_heures' => env('RDV_DELAI_REPONSE_MEDECIN_HEURES', 24),
    'duree_consultation_defaut_min' => env('RDV_DUREE_CONSULTATION_DEFAUT_MIN', 30),
    'rappel_heures_avant' => env('RDV_RAPPEL_HEURES_AVANT', '24,1'),
];
