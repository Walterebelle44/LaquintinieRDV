<script setup lang="ts">
import { useApi } from '~/composables/useApi';

definePageMeta({ layout: "blank" as any, roles: ["medecin"] });
const nav = [
  { label: "Vue d'ensemble", to: "/espace-medecin", icon: "activity" },
  { label: "Demandes de RDV", to: "/espace-medecin/demandes", icon: "bell" },
  { label: "Mon planning", to: "/espace-medecin/planning", icon: "calendar" },
  { label: "Mon profil", to: "/espace-medecin/profil", icon: "user" },
];

const api = useApi();
const { user, fetchMe } = useAuth();

const form = ref({
  prenom: user.value?.prenom || "",
  nom: user.value?.nom || "",
  email: user.value?.email || "",
  telephone: user.value?.telephone || "",
});

const saving = ref(false);
const saved = ref(false);
const erreur = ref("");

async function save() {
  erreur.value = "";
  saving.value = true;
  try {
    await api.put("/mon-profil", form.value);
    await fetchMe();
    saved.value = true;
    setTimeout(() => (saved.value = false), 2500);
  } catch (e: any) {
    erreur.value = e?.message || "Impossible d'enregistrer les modifications.";
  } finally {
    saving.value = false;
  }
}

const pwd = ref({ actuel: "", nouveau: "", confirmation: "" });
const pwdSaving = ref(false);
const pwdMessage = ref("");
const pwdErreur = ref(false);

async function updatePassword() {
  pwdMessage.value = "";
  pwdSaving.value = true;
  try {
    await api.put("/mon-mot-de-passe", {
      mot_de_passe_actuel: pwd.value.actuel,
      mot_de_passe: pwd.value.nouveau,
      mot_de_passe_confirmation: pwd.value.confirmation,
    });
    pwdMessage.value = "Mot de passe mis à jour.";
    pwdErreur.value = false;
    pwd.value = { actuel: "", nouveau: "", confirmation: "" };
  } catch (e: any) {
    pwdMessage.value = e?.message || "Impossible de mettre à jour le mot de passe.";
    pwdErreur.value = true;
  } finally {
    pwdSaving.value = false;
  }
}
</script>

<template>
  <DashboardShell role="medecin" :nav="nav" title="Mon profil">
    <div class="grid lg:grid-cols-[280px_1fr] gap-6">
      <div class="card p-6 text-center h-fit">
        <div class="w-24 h-24 rounded-full bg-azure-100 text-azure-700 flex items-center justify-center font-display font-bold text-2xl mx-auto">
          {{ form.prenom[0] }}{{ form.nom[0] }}
        </div>
        <p class="font-display font-semibold text-ink-950 mt-4">Dr {{ form.prenom }} {{ form.nom }}</p>
      </div>

      <div class="space-y-6">
        <div v-if="erreur" class="p-3.5 rounded-xl bg-clay-soft text-clay text-sm">{{ erreur }}</div>

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
            <div class="flex items-center gap-3">
              <button class="btn-primary" :disabled="saving" @click="save">{{ saving ? "Enregistrement…" : "Enregistrer les modifications" }}</button>
              <span v-if="saved" class="text-sm text-emerald-600 flex items-center gap-1.5"><Icon name="check" class="w-4 h-4" />Enregistré</span>
            </div>
          </div>
        </div>

        <div class="card p-6 sm:p-7">
          <h2 class="font-display font-semibold text-ink-900 mb-5">Sécurité</h2>
          <div class="space-y-4">
            <div><label class="label">Mot de passe actuel</label><input v-model="pwd.actuel" type="password" class="input" /></div>
            <div class="grid sm:grid-cols-2 gap-4">
              <div><label class="label">Nouveau mot de passe</label><input v-model="pwd.nouveau" type="password" class="input" placeholder="••••••••" /></div>
              <div><label class="label">Confirmer le mot de passe</label><input v-model="pwd.confirmation" type="password" class="input" placeholder="••••••••" /></div>
            </div>
          </div>
          <p v-if="pwdMessage" class="text-sm mt-3" :class="pwdErreur ? 'text-clay' : 'text-emerald-600'">{{ pwdMessage }}</p>
          <button class="btn-secondary mt-5" :disabled="pwdSaving" @click="updatePassword">
            {{ pwdSaving ? "Mise à jour…" : "Mettre à jour le mot de passe" }}
          </button>
        </div>
      </div>
    </div>
  </DashboardShell>
</template>
