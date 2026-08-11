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
const jours = [
  { label: "Lundi", valeur: 1 }, { label: "Mardi", valeur: 2 }, { label: "Mercredi", valeur: 3 },
  { label: "Jeudi", valeur: 4 }, { label: "Vendredi", valeur: 5 }, { label: "Samedi", valeur: 6 },
];

const { data: dispoRes, refresh: refreshDispo } = await useAsyncData("mes-disponibilites", () =>
  api.get("/medecin/disponibilites").catch(() => ({ data: [] }))
);

const grille = ref(
  jours.map((j) => {
    const existant = (dispoRes.value?.data || []).find((d: any) => d.jour_semaine === j.valeur);
    return {
      jour: j.label,
      valeur: j.valeur,
      actif: !!existant,
      debut: existant?.heure_debut?.slice(0, 5) || "08:00",
      fin: existant?.heure_fin?.slice(0, 5) || "15:00",
    };
  })
);

const savingDispo = ref(false);
const dispoMessage = ref("");

async function enregistrerDisponibilites() {
  savingDispo.value = true;
  dispoMessage.value = "";
  try {
    const creneaux = grille.value
      .filter((g) => g.actif)
      .map((g) => ({ jour_semaine: g.valeur, heure_debut: g.debut, heure_fin: g.fin }));
    await api.put("/medecin/disponibilites", { creneaux });
    await refreshDispo();
    dispoMessage.value = "Disponibilités mises à jour.";
  } catch (e: any) {
    dispoMessage.value = e?.message || "Impossible d'enregistrer.";
  } finally {
    savingDispo.value = false;
  }
}

// Blocage ponctuel
const blocage = ref({ date: "", heureDebut: "", heureFin: "", motif: "" });
const blocageSaving = ref(false);
const blocageMessage = ref("");

async function bloquerCreneau() {
  if (!blocage.value.date || !blocage.value.heureDebut || !blocage.value.heureFin) return;
  blocageSaving.value = true;
  blocageMessage.value = "";
  try {
    await api.post("/medecin/indisponibilites", {
      debut: `${blocage.value.date} ${blocage.value.heureDebut}:00`,
      fin: `${blocage.value.date} ${blocage.value.heureFin}:00`,
      motif: blocage.value.motif || null,
    });
    blocageMessage.value = "Créneau bloqué avec succès.";
    blocage.value = { date: "", heureDebut: "", heureFin: "", motif: "" };
  } catch (e: any) {
    blocageMessage.value = e?.message || "Impossible de bloquer ce créneau.";
  } finally {
    blocageSaving.value = false;
  }
}
</script>

<template>
  <DashboardShell role="medecin" :nav="nav" title="Mon planning">
    <div class="grid lg:grid-cols-[1fr_360px] gap-6">
      <div class="card p-6 sm:p-7">
        <h2 class="font-display font-semibold text-ink-900 mb-1">Disponibilités récurrentes</h2>
        <p class="text-sm text-ink-500 mb-5">Cochez les jours travaillés et définissez vos horaires de consultation.</p>

        <div class="space-y-2.5">
          <div v-for="g in grille" :key="g.valeur" class="flex flex-col sm:flex-row sm:items-center gap-3 p-3.5 rounded-xl border" :class="g.actif ? 'border-azure-200 bg-azure-50/40' : 'border-ink-100'">
            <label class="flex items-center gap-2.5 w-32 shrink-0 cursor-pointer">
              <input type="checkbox" v-model="g.actif" class="rounded border-ink-300 text-azure-600 focus:ring-azure-200" />
              <span class="text-sm font-medium text-ink-800">{{ g.jour }}</span>
            </label>
            <div class="flex items-center gap-2" v-if="g.actif">
              <input type="time" v-model="g.debut" class="input !py-1.5 !w-28 text-sm" />
              <span class="text-ink-400 text-sm">à</span>
              <input type="time" v-model="g.fin" class="input !py-1.5 !w-28 text-sm" />
            </div>
          </div>
        </div>

        <div class="flex items-center gap-3 mt-5">
          <button class="btn-primary !text-sm" :disabled="savingDispo" @click="enregistrerDisponibilites">
            {{ savingDispo ? "Enregistrement…" : "Enregistrer mes disponibilités" }}
          </button>
          <span v-if="dispoMessage" class="text-sm text-emerald-600">{{ dispoMessage }}</span>
        </div>
      </div>

      <div class="space-y-6">
        <div class="card p-6">
          <h2 class="font-display font-semibold text-ink-900 mb-3">Bloquer un créneau</h2>
          <p class="text-sm text-ink-500 mb-4">Urgence ou absence imprévue ? Bloquez rapidement une plage horaire.</p>
          <div class="space-y-3">
            <input v-model="blocage.date" type="date" class="input" />
            <div class="grid grid-cols-2 gap-3">
              <input v-model="blocage.heureDebut" type="time" class="input" />
              <input v-model="blocage.heureFin" type="time" class="input" />
            </div>
            <input v-model="blocage.motif" type="text" class="input" placeholder="Motif (optionnel)" />
            <button class="btn-primary w-full !text-sm" :disabled="blocageSaving" @click="bloquerCreneau">
              {{ blocageSaving ? "Blocage…" : "Bloquer ce créneau" }}
            </button>
            <p v-if="blocageMessage" class="text-sm" :class="blocageMessage.includes('succès') ? 'text-emerald-600' : 'text-clay'">{{ blocageMessage }}</p>
          </div>
        </div>
      </div>
    </div>
  </DashboardShell>
</template>
