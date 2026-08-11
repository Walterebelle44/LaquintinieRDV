<script setup lang="ts">
import { useApi } from '~/composables/useApi';
import { mapMedecin, mapRendezVous } from '~/composables/useMappers';

definePageMeta({ layout: "blank" as any, roles: ["patient"] });
const nav = [
  { label: "Vue d'ensemble", to: "/espace-client", icon: "activity" },
  { label: "Mes rendez-vous", to: "/espace-client/rendez-vous", icon: "calendar" },
  { label: "Trouver un médecin", to: "/medecins", icon: "search" },
  { label: "Mon profil", to: "/espace-client/profil", icon: "user" },
];

const api = useApi();
const { data: rdvRes, refresh } = await useAsyncData("mes-rdv-overview", () =>
  api.get("/patient/rendez-vous").catch(() => ({ data: [] }))
);

const rdvs = computed(() => (rdvRes.value?.data || []).map((r: any) => ({
  ...mapRendezVous(r),
  medecin: mapMedecin(r.medecin),
})));

const aVenir = computed(() => rdvs.value.filter((r: { statut: string; }) => ["en_attente", "confirme", "reprogramme"].includes(r.statut)));
const passes = computed(() => rdvs.value.filter((r: { statut: string; }) => r.statut === "termine"));
const prochain = computed(() => aVenir.value[0]);

const annulation = ref(false);
async function annulerProchain() {
  if (!prochain.value) return;
  annulation.value = true;
  try {
    await api.post(`/patient/rendez-vous/${prochain.value.id}/annuler`);
    await refresh();
  } catch (e: any) {
    alert(e?.message || "Impossible d'annuler ce rendez-vous.");
  } finally {
    annulation.value = false;
  }
}

async function telechargerTicket() {
  if (!prochain.value) return;
  try {
    const blob: Blob = await $fetch(`/patient/rendez-vous/${prochain.value.id}/ticket`, {
      baseURL: useRuntimeConfig().public.apiBase as string | undefined,
      headers: { Authorization: `Bearer ${api.token.value}` },
      responseType: "blob",
    });
    const url = URL.createObjectURL(blob);
    const a = document.createElement("a");
    a.href = url;
    a.download = `ticket-${prochain.value.ticket || prochain.value.id}.pdf`;
    a.click();
    URL.revokeObjectURL(url);
  } catch (e: any) {
    alert(e?.message || "Ticket indisponible : le rendez-vous n'est pas encore confirmé.");
  }
}
</script>

<template>
  <DashboardShell role="patient" :nav="nav" title="Vue d'ensemble">
    <div class="grid lg:grid-cols-3 gap-6 mb-8">
      <StatCard label="Rendez-vous à venir" :value="String(aVenir.length)" icon="calendar" />
      <StatCard label="Consultations passées" :value="String(passes.length)" icon="check-circle" color="bg-pulse-soft text-emerald-600" />
      <StatCard label="Total rendez-vous" :value="String(rdvs.length)" icon="users" color="bg-amber-50 text-amber-600" />
    </div>

    <div class="grid lg:grid-cols-[1.3fr_1fr] gap-6">
      <!-- Prochain RDV -->
      <div class="card p-6 sm:p-7" v-if="prochain">
        <div class="flex items-center justify-between mb-5">
          <h2 class="font-display font-semibold text-ink-900">Prochain rendez-vous</h2>
          <span class="badge" :class="prochain.statut === 'confirme' ? 'badge-confirmed' : 'badge-pending'">
            {{ prochain.statut === 'confirme' ? 'Confirmé' : 'En attente' }}
          </span>
        </div>
        <div class="flex items-center gap-4">
          <img :src="prochain.medecin.photo" class="w-16 h-16 rounded-xl object-cover" alt="" />
          <div class="flex-1">
            <p class="font-display font-semibold text-ink-950">Dr {{ prochain.medecin.prenom }} {{ prochain.medecin.nom }}</p>
            <p class="text-sm text-ink-500">{{ prochain.motif || 'Consultation' }}</p>
          </div>
        </div>
        <div class="grid grid-cols-2 gap-4 mt-6 pt-6 border-t border-ink-100">
          <div class="flex items-center gap-2 text-sm text-ink-700"><Icon name="calendar" class="w-4 h-4 text-azure-600" />{{ new Date(prochain.date).toLocaleDateString('fr-FR', {day:'numeric', month:'long'}) }}</div>
          <div class="flex items-center gap-2 text-sm text-ink-700"><Icon name="clock" class="w-4 h-4 text-azure-600" />{{ prochain.heure }}</div>
        </div>
        <div class="flex gap-3 mt-6">
          <button class="btn-secondary flex-1" @click="telechargerTicket"><Icon name="printer" class="w-4 h-4" />Ticket</button>
          <button class="btn-secondary flex-1 !text-clay !border-clay/20 hover:!bg-clay/5" :disabled="annulation" @click="annulerProchain">
            {{ annulation ? "..." : "Annuler" }}
          </button>
        </div>
      </div>
      <div class="card p-10 text-center text-sm text-ink-500" v-else>
        Aucun rendez-vous à venir pour le moment.
      </div>

      <!-- Actions rapides -->
      <div class="card p-6 sm:p-7">
        <h2 class="font-display font-semibold text-ink-900 mb-5">Actions rapides</h2>
        <div class="space-y-3">
          <NuxtLink to="/medecins" class="flex items-center gap-3 p-3.5 rounded-xl border border-ink-100 hover:border-azure-200 hover:bg-azure-50/50 transition-colors group">
            <span class="w-10 h-10 rounded-lg bg-azure-50 text-azure-600 flex items-center justify-center group-hover:bg-azure-600 group-hover:text-white transition-colors"><Icon name="plus" class="w-4.5 h-4.5" /></span>
            <div class="flex-1"><p class="text-sm font-semibold text-ink-800">Nouveau rendez-vous</p><p class="text-xs text-ink-400">Trouver un médecin disponible</p></div>
            <Icon name="chevron-right" class="w-4 h-4 text-ink-300" />
          </NuxtLink>
          <NuxtLink to="/espace-client/rendez-vous" class="flex items-center gap-3 p-3.5 rounded-xl border border-ink-100 hover:border-azure-200 hover:bg-azure-50/50 transition-colors group">
            <span class="w-10 h-10 rounded-lg bg-azure-50 text-azure-600 flex items-center justify-center group-hover:bg-azure-600 group-hover:text-white transition-colors"><Icon name="calendar" class="w-4.5 h-4.5" /></span>
            <div class="flex-1"><p class="text-sm font-semibold text-ink-800">Historique complet</p><p class="text-xs text-ink-400">Voir tous mes rendez-vous</p></div>
            <Icon name="chevron-right" class="w-4 h-4 text-ink-300" />
          </NuxtLink>
          <NuxtLink to="/espace-client/profil" class="flex items-center gap-3 p-3.5 rounded-xl border border-ink-100 hover:border-azure-200 hover:bg-azure-50/50 transition-colors group">
            <span class="w-10 h-10 rounded-lg bg-azure-50 text-azure-600 flex items-center justify-center group-hover:bg-azure-600 group-hover:text-white transition-colors"><Icon name="user" class="w-4.5 h-4.5" /></span>
            <div class="flex-1"><p class="text-sm font-semibold text-ink-800">Mettre à jour mon profil</p><p class="text-xs text-ink-400">Coordonnées, mot de passe</p></div>
            <Icon name="chevron-right" class="w-4 h-4 text-ink-300" />
          </NuxtLink>
        </div>
      </div>
    </div>
  </DashboardShell>
</template>
