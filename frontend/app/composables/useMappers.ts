/**
 * Adapte les objets renvoyés par l'API Laravel vers la forme attendue par
 * les composants (DoctorCard, SpecialiteCard...), pour ne pas avoir à
 * réécrire les composants d'affichage.
 */
export function mapSpecialite(s: any) {
  return {
    id: s.slug,
    nom: s.nom,
    icone: s.icone || "heart",
    description: s.description || "",
    nbMedecins: s.medecins_count ?? 0,
  };
}

export function mapMedecin(m: any) {
  const specialitePrincipale = m.specialites?.[0];
  return {
    id: String(m.id),
    nom: m.user?.nom || "",
    prenom: m.user?.prenom || "",
    specialiteId: specialitePrincipale?.slug || "",
    specialiteNom: specialitePrincipale?.nom || "",
    photo: m.user?.photo || `https://i.pravatar.cc/300?u=medecin-${m.id}`,
    bio: m.biographie || "",
    experience: m.annees_experience || 0,
    note: 4.8,
    avis: 0,
    prochaineDispo: "Voir les créneaux disponibles",
    tarif: m.tarif_consultation || 0,
    statut: m.user?.statut || "actif",
    joursDispo: [] as string[],
    salle: m.salle || null,
  };
}

export function mapRendezVous(r: any) {
  return {
    id: String(r.id),
    uuid: r.uuid,
    patient: r.patient ? `${r.patient.prenom} ${r.patient.nom}` : "",
    medecinId: String(r.medecin_id ?? r.medecin?.id ?? ""),
    date: r.date,
    heure: (r.heure_debut || "").slice(0, 5),
    motif: r.motif || "",
    statut: r.statut,
    motifRefus: r.motif_refus || null,
    ticket: r.ticket?.numero || null,
  };
}
