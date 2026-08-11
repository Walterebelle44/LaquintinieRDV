<script setup lang="ts">
import { useApi } from '~/composables/useApi';
import { mapRendezVous } from '~/composables/useMappers';

definePageMeta({ layout: "blank" as any, roles: ["medecin"] });
const nav = [
  { label: "Vue d'ensemble", to: "/espace-medecin", icon: "activity" },
  { label: "Demandes de RDV", to: "/espace-medecin/demandes", icon: "bell" },
  { label: "Mon planning", to: "/espace-medecin/planning", icon: "calendar" },
  { label: "Mon profil", to: "/espace-medecin/profil", icon: "user" },
];

const api = useApi();
const { user } = useAuth();

const { data: rdvRes, refresh } = await useAsyncData("medecin-rdv-overview", () =>
  api.get("/medecin/rendez-vous").catch(() => ({ data: [] }))
);

const rdvs = computed(() => (rdvRes.value?.data || []).map(mapRendezVous));
const today = new Date().toISOString().slice(0, 10);
const rdvsAujourdhui = computed(() => rdvs.value.filter((r: { date: string; }) => r.date === today));
const enAttente = computed(() => rdvs.value.filter((r: { statut: string; }) => r.statut === "en_attente"));

const statusLabel: Record<string, string> = {
  en_attente: "En attente", confirme: "Confirmé", reprogramme: "Reprogrammé",
  termine: "Terminé", refuse: "Refusé", annule: "Annulé", absent: "Absent",
};

const actionEnCours = ref<string | null>(null);
async function repondre(id: string, action: "accepter" | "refuser") {
  actionEnCours.value = id;
  try {
    const payload: any = { action };
    if (action === "refuser") {
      const motif = prompt("Motif du refus (visible par le patient) :");
      if (motif === null) { actionEnCours.value = null; return; }
      payload.motif_refus = motif || "Non précisé";
    }
    await api.post(`/medecin/rendez-vous/${id}/repondre`, payload);
    await refresh();
  } catch (e: any) {
    alert(e?.message || "Action impossible.");
  } finally {
    actionEnCours.value = null;
  }
}
</script>

<template>
  <DashboardShell role="medecin" :nav="nav" :title="`Bonjour, Dr ${user?.nom || ''}`">
    <div class="grid lg:grid-cols-4 gap-6 mb-8">
      <StatCard label="RDV aujourd'hui" :value="String(rdvsAujourdhui.length)" icon="calendar" />
      <StatCard label="Demandes en attente" :value="String(enAttente.length)" icon="bell" color="bg-amber-50 text-amber-600" />
      <StatCard label="Total rendez-vous" :value="String(rdvs.length)" icon="users" />
      <StatCard label="Confirmés" :value="String(rdvs.filter((r: { statut: string; }) => r.statut === 'confirme').length)" icon="trending-up" color="bg-pulse-soft text-emerald-600" />
    </div>

    <div class="grid lg:grid-cols-[1.3fr_1fr] gap-6">
      <div class="card p-6 sm:p-7">
        <div class="flex items-center justify-between mb-5">
          <h2 class="font-display font-semibold text-ink-900">Planning du jour</h2>
          <NuxtLink to="/espace-medecin/planning" class="text-sm text-azure-600 font-medium hover:text-azure-700">Vue complète</NuxtLink>
        </div>
        <div v-if="!rdvsAujourdhui.length" class="text-sm text-ink-400 py-8 text-center">Aucun rendez-vous aujourd'hui.</div>
        <div class="space-y-3">
          <div v-for="r in rdvsAujourdhui" :key="r.id" class="flex items-center gap-4 p-3 rounded-xl hover:bg-ink-50 transition-colors">
            <div class="w-14 text-center shrink-0">
              <p class="font-display font-bold text-ink-900 text-sm">{{ r.heure }}</p>
            </div>
            <div class="w-px h-10 bg-ink-100" />
            <div class="flex-1 min-w-0">
              <p class="font-medium text-ink-800 text-sm">{{ r.patient }}</p>
              <p class="text-xs text-ink-400">{{ r.motif || "Consultation" }}</p>
            </div>
            <span class="badge" :class="{ 'badge-confirmed': r.statut === 'confirme', 'badge-pending': r.statut === 'en_attente', 'badge-done': r.statut === 'termine' }">
              {{ statusLabel[r.statut] }}
            </span>
          </div>
        </div>
      </div>

      <div class="card p-6 sm:p-7">
        <h2 class="font-display font-semibold text-ink-900 mb-5">Demandes à traiter</h2>
        <div v-if="!enAttente.length" class="text-sm text-ink-400 py-8 text-center">Aucune demande en attente. 🎉</div>
        <div class="space-y-3">
          <div v-for="r in enAttente" :key="r.id" class="p-4 rounded-xl border border-amber-100 bg-amber-50/50">
            <p class="font-medium text-ink-800 text-sm">{{ r.patient }}</p>
            <p class="text-xs text-ink-500 mt-0.5">{{ new Date(r.date).toLocaleDateString('fr-FR', {day:'numeric', month:'short'}) }} à {{ r.heure }} · {{ r.motif || "Consultation" }}</p>
            <div class="flex gap-2 mt-3">
              <button class="btn-primary !py-1.5 !px-3 !text-xs flex-1" :disabled="actionEnCours === r.id" @click="repondre(r.id, 'accepter')">
                <Icon name="check" class="w-3.5 h-3.5" />Accepter
              </button>
              <button class="btn-secondary !py-1.5 !px-3 !text-xs flex-1 !text-clay !border-clay/20" :disabled="actionEnCours === r.id" @click="repondre(r.id, 'refuser')">
                <Icon name="x" class="w-3.5 h-3.5" />Refuser
              </button>
            </div>
          </div>
          <NuxtLink to="/espace-medecin/demandes" class="block text-center text-sm text-azure-600 font-medium hover:text-azure-700 pt-2">
            Voir toutes les demandes →
          </NuxtLink>
        </div>
      </div>
    </div>
  </DashboardShell>
</template>
