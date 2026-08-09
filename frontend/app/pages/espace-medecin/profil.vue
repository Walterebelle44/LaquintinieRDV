<script setup lang="ts">
const nav = [
  { label: "Vue d'ensemble", to: "/espace-medecin", icon: "activity" },
  { label: "Demandes de RDV", to: "/espace-medecin/demandes", icon: "bell" },
  { label: "Mon planning", to: "/espace-medecin/planning", icon: "calendar" },
  { label: "Mon profil", to: "/espace-medecin/profil", icon: "user" },
];
const form = ref({
  prenom: "Jean-Paul", nom: "Ekwalla", specialite: "Cardiologie",
  email: "j.ekwalla@laquintinie.cm", telephone: "+237 6 90 11 22 33",
  bio: "Spécialiste des pathologies cardiovasculaires, 15 ans d'expérience au CHU.",
  duree: "30",
});
</script>

<template>
  <DashboardShell role="medecin" :nav="nav" title="Mon profil">
    <div class="grid lg:grid-cols-[280px_1fr] gap-6">
      <div class="card p-6 text-center h-fit">
        <img src="https://i.pravatar.cc/300?img=12" class="w-24 h-24 rounded-full object-cover mx-auto" alt="" />
        <p class="font-display font-semibold text-ink-950 mt-4">Dr {{ form.prenom }} {{ form.nom }}</p>
        <span class="badge bg-azure-50 text-azure-700 mt-2"><Icon name="heart" class="w-3.5 h-3.5" />{{ form.specialite }}</span>
        <button class="btn-secondary w-full mt-5 !text-sm">Changer la photo</button>
      </div>

      <div class="space-y-6">
        <div class="card p-6 sm:p-7">
          <h2 class="font-display font-semibold text-ink-900 mb-5">Informations professionnelles</h2>
          <div class="space-y-5">
            <div class="grid sm:grid-cols-2 gap-4">
              <div><label class="label">Prénom</label><input v-model="form.prenom" class="input" /></div>
              <div><label class="label">Nom</label><input v-model="form.nom" class="input" /></div>
            </div>
            <div class="grid sm:grid-cols-2 gap-4">
              <div><label class="label">Email professionnel</label><input v-model="form.email" class="input" /></div>
              <div><label class="label">Téléphone</label><input v-model="form.telephone" class="input" /></div>
            </div>
            <div><label class="label">Biographie courte</label><textarea v-model="form.bio" rows="3" class="input"></textarea></div>
            <div class="grid sm:grid-cols-2 gap-4">
              <div><label class="label">Durée de consultation par défaut (min)</label><input v-model="form.duree" type="number" class="input" /></div>
              <div><label class="label">Spécialité</label>
                <select v-model="form.specialite" class="input">
                  <option>Cardiologie</option><option>Médecine générale</option><option>Dentaire</option>
                </select>
              </div>
            </div>
            <button class="btn-primary">Enregistrer les modifications</button>
          </div>
        </div>

        <div class="card p-6 sm:p-7">
          <h2 class="font-display font-semibold text-ink-900 mb-5">Sécurité</h2>
          <div class="grid sm:grid-cols-2 gap-4">
            <div><label class="label">Nouveau mot de passe</label><input type="password" class="input" placeholder="••••••••" /></div>
            <div><label class="label">Confirmer le mot de passe</label><input type="password" class="input" placeholder="••••••••" /></div>
          </div>
          <button class="btn-secondary mt-5">Mettre à jour le mot de passe</button>
        </div>
      </div>
    </div>
  </DashboardShell>
</template>
