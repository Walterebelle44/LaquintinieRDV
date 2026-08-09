export interface Specialite {
  id: string;
  nom: string;
  icone: string;
  description: string;
  nbMedecins: number;
}

export interface Medecin {
  id: string;
  nom: string;
  prenom: string;
  specialiteId: string;
  photo: string;
  bio: string;
  experience: number;
  note: number;
  avis: number;
  prochaineDispo: string;
  tarif: number;
  statut: "actif" | "bloque";
  joursDispo: string[];
}

export interface RendezVous {
  id: string;
  patient: string;
  medecinId: string;
  date: string;
  heure: string;
  motif: string;
  statut: "en_attente" | "confirme" | "refuse" | "termine" | "annule";
  ticket: string;
}

export const useSpecialites = (): Specialite[] => [
  { id: "cardio", nom: "Cardiologie", icone: "heart", description: "Cœur & système vasculaire", nbMedecins: 6 },
  { id: "general", nom: "Médecine générale", icone: "stethoscope", description: "Consultations générales", nbMedecins: 14 },
  { id: "dentaire", nom: "Dentaire", icone: "tooth", description: "Soins bucco-dentaires", nbMedecins: 5 },
  { id: "pediatrie", nom: "Pédiatrie", icone: "baby", description: "Santé de l'enfant", nbMedecins: 8 },
  { id: "gyneco", nom: "Gynécologie", icone: "flower", description: "Santé de la femme", nbMedecins: 7 },
  { id: "dermato", nom: "Dermatologie", icone: "sparkle", description: "Peau, cheveux & ongles", nbMedecins: 4 },
  { id: "ophtalmo", nom: "Ophtalmologie", icone: "eye", description: "Yeux & vision", nbMedecins: 3 },
  { id: "ortho", nom: "Orthopédie", icone: "bone", description: "Os, muscles & articulations", nbMedecins: 5 },
];

export const useMedecins = (): Medecin[] => [
  { id: "m1", nom: "Ekwalla", prenom: "Jean-Paul", specialiteId: "cardio", photo: "https://i.pravatar.cc/300?img=12", bio: "Spécialiste des pathologies cardiovasculaires, 15 ans d'expérience au CHU.", experience: 15, note: 4.9, avis: 128, prochaineDispo: "Aujourd'hui, 15h30", tarif: 15000, statut: "actif", joursDispo: ["Lun", "Mar", "Jeu", "Ven"] },
  { id: "m2", nom: "Mballa", prenom: "Aïcha", specialiteId: "general", photo: "https://i.pravatar.cc/300?img=32", bio: "Médecin généraliste, prise en charge globale et suivi de famille.", experience: 9, note: 4.8, avis: 203, prochaineDispo: "Demain, 9h00", tarif: 8000, statut: "actif", joursDispo: ["Lun", "Mar", "Mer", "Jeu", "Ven"] },
  { id: "m3", nom: "Tchoumi", prenom: "Bernard", specialiteId: "dentaire", photo: "https://i.pravatar.cc/300?img=51", bio: "Chirurgien-dentiste, spécialisé en soins conservateurs et esthétique dentaire.", experience: 11, note: 4.7, avis: 96, prochaineDispo: "Aujourd'hui, 17h00", tarif: 12000, statut: "actif", joursDispo: ["Mar", "Mer", "Ven", "Sam"] },
  { id: "m4", nom: "Njoya", prenom: "Fatimatou", specialiteId: "pediatrie", photo: "https://i.pravatar.cc/300?img=45", bio: "Pédiatre passionnée par le suivi néonatal et la vaccination infantile.", experience: 7, note: 5.0, avis: 154, prochaineDispo: "Demain, 10h15", tarif: 10000, statut: "actif", joursDispo: ["Lun", "Mer", "Jeu", "Ven"] },
  { id: "m5", nom: "Etoundi", prenom: "Marceline", specialiteId: "gyneco", photo: "https://i.pravatar.cc/300?img=47", bio: "Gynécologue-obstétricienne, suivi de grossesse et santé reproductive.", experience: 13, note: 4.9, avis: 171, prochaineDispo: "Lun. 11 août, 8h30", tarif: 14000, statut: "actif", joursDispo: ["Lun", "Mar", "Jeu"] },
  { id: "m6", nom: "Fouda", prenom: "Robert", specialiteId: "dermato", photo: "https://i.pravatar.cc/300?img=14", bio: "Dermatologue, prise en charge des affections cutanées et cosmétiques.", experience: 8, note: 4.6, avis: 62, prochaineDispo: "Mer. 13 août, 14h00", tarif: 13000, statut: "actif", joursDispo: ["Mer", "Ven"] },
  { id: "m7", nom: "Belinga", prenom: "Solange", specialiteId: "ophtalmo", photo: "https://i.pravatar.cc/300?img=25", bio: "Ophtalmologue, chirurgie de la cataracte et troubles de la réfraction.", experience: 10, note: 4.8, avis: 88, prochaineDispo: "Demain, 16h00", tarif: 12500, statut: "actif", joursDispo: ["Lun", "Mar", "Ven"] },
  { id: "m8", nom: "Kamdem", prenom: "Hervé", specialiteId: "ortho", photo: "https://i.pravatar.cc/300?img=33", bio: "Chirurgien orthopédiste, traumatologie du sport et prothèses articulaires.", experience: 12, note: 4.7, avis: 74, prochaineDispo: "Jeu. 14 août, 9h30", tarif: 16000, statut: "actif", joursDispo: ["Mar", "Jeu", "Sam"] },
  { id: "m9", nom: "Onana", prenom: "Christelle", specialiteId: "general", photo: "https://i.pravatar.cc/300?img=48", bio: "Médecin généraliste, consultations rapides et orientation spécialisée.", experience: 5, note: 4.5, avis: 41, prochaineDispo: "Aujourd'hui, 18h00", tarif: 8000, statut: "actif", joursDispo: ["Lun", "Mer", "Ven", "Sam"] },
];

export const useRendezVous = (): RendezVous[] => [
  { id: "rdv1", patient: "Walter Djoko", medecinId: "m1", date: "2026-08-07", heure: "15:30", motif: "Douleurs thoraciques", statut: "confirme", ticket: "LQT-2026-00841" },
  { id: "rdv2", patient: "Walter Djoko", medecinId: "m3", date: "2026-08-14", heure: "17:00", motif: "Détartrage", statut: "en_attente", ticket: "LQT-2026-00842" },
  { id: "rdv3", patient: "Marie Essomba", medecinId: "m1", date: "2026-08-06", heure: "09:00", motif: "Contrôle tension", statut: "en_attente", ticket: "LQT-2026-00839" },
  { id: "rdv4", patient: "Paul Biya Jr.", medecinId: "m1", date: "2026-08-06", heure: "11:00", motif: "Suivi post-opératoire", statut: "confirme", ticket: "LQT-2026-00812" },
  { id: "rdv5", patient: "Sarah Ngono", medecinId: "m1", date: "2026-07-28", heure: "10:00", motif: "Consultation cardio", statut: "termine", ticket: "LQT-2026-00701" },
  { id: "rdv6", patient: "Alain Fotso", medecinId: "m2", date: "2026-08-05", heure: "16:00", motif: "Fièvre persistante", statut: "refuse", ticket: "LQT-2026-00799" },
];

export const useLogs = () => [
  { id: "l1", auteur: "admin@laquintinie.cm", action: "Blocage du compte médecin m6", date: "2026-08-06 08:12", type: "securite" },
  { id: "l2", auteur: "j.ekwalla@laquintinie.cm", action: "Rendez-vous LQT-2026-00841 confirmé", date: "2026-08-06 07:50", type: "rdv" },
  { id: "l3", auteur: "walter.d@gmail.com", action: "Connexion réussie", date: "2026-08-06 07:22", type: "connexion" },
  { id: "l4", auteur: "admin@laquintinie.cm", action: "Ajout de la spécialité « Endocrinologie »", date: "2026-08-05 17:40", type: "config" },
  { id: "l5", auteur: "inconnu", action: "Tentative de connexion échouée (mot de passe)", date: "2026-08-05 14:03", type: "alerte" },
  { id: "l6", auteur: "admin@laquintinie.cm", action: "Export du rapport mensuel (PDF)", date: "2026-08-01 09:15", type: "export" },
];
