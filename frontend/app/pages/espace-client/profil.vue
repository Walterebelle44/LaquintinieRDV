<script setup lang="ts">
const nav = [
  { label: "Vue d'ensemble", to: "/espace-client", icon: "activity" },
  { label: "Mes rendez-vous", to: "/espace-client/rendez-vous", icon: "calendar" },
  { label: "Trouver un médecin", to: "/medecins", icon: "search" },
  { label: "Mon profil", to: "/espace-client/profil", icon: "user" },
];

const { name } = useAuth();
const form = ref({
  prenom: "Walter",
  nom: "Djoko",
  email: "walter.d@gmail.com",
  telephone: "+237 6 77 12 34 56",
});
const saved = ref(false);
function save() {
  saved.value = true;
  setTimeout(() => (saved.value = false), 2500);
}
</script>

<template>
  <DashboardShell role="client" :nav="nav" title="Mon profil">
    <div class="grid lg:grid-cols-[280px_1fr] gap-6">
      <div class="card p-6 text-center h-fit">
        <div class="w-24 h-24 rounded-full bg-azure-100 text-azure-700 flex items-center justify-center font-display font-bold text-2xl mx-auto">
          {{ form.prenom[0] }}{{ form.nom[0] }}
        </div>
        <p class="font-display font-semibold text-ink-950 mt-4">{{ form.prenom }} {{ form.nom }}</p>
        <p class="text-sm text-ink-500">Patient depuis janvier 2025</p>
        <button class="btn-secondary w-full mt-5 !text-sm">Changer la photo</button>
      </div>

      <div class="space-y-6">
        <div class="card p-6 sm:p-7">
          <h2 class="font-display font-semibold text-ink-900 mb-5">Informations personnelles</h2>
          <form @submit.prevent="save" class="space-y-5">
            <div class="grid sm:grid-cols-2 gap-4">
              <div><label class="label">Prénom</label><input v-model="form.prenom" class="input" /></div>
              <div><label class="label">Nom</label><input v-model="form.nom" class="input" /></div>
            </div>
            <div><label class="label">Adresse email</label><input v-model="form.email" type="email" class="input" /></div>
            <div><label class="label">Numéro de téléphone</label><input v-model="form.telephone" type="tel" class="input" /></div>
            <div class="flex items-center gap-3 pt-2">
              <button type="submit" class="btn-primary">Enregistrer les modifications</button>
              <span v-if="saved" class="text-sm text-emerald-600 flex items-center gap-1.5"><Icon name="check" class="w-4 h-4" />Enregistré</span>
            </div>
          </form>
        </div>

        <div class="card p-6 sm:p-7">
          <h2 class="font-display font-semibold text-ink-900 mb-5">Sécurité</h2>
          <div class="grid sm:grid-cols-2 gap-4">
            <div><label class="label">Nouveau mot de passe</label><input type="password" class="input" placeholder="••••••••" /></div>
            <div><label class="label">Confirmer le mot de passe</label><input type="password" class="input" placeholder="••••••••" /></div>
          </div>
          <button class="btn-secondary mt-5">Mettre à jour le mot de passe</button>
        </div>

        <div class="card p-6 sm:p-7 border-clay/20">
          <h2 class="font-display font-semibold text-clay mb-2">Zone sensible</h2>
          <p class="text-sm text-ink-500 mb-4">La suppression de votre compte est définitive et efface l'historique de vos rendez-vous.</p>
          <button class="btn-secondary !text-clay !border-clay/30 hover:!bg-clay/5">Supprimer mon compte</button>
        </div>
      </div>
    </div>
  </DashboardShell>
</template>
