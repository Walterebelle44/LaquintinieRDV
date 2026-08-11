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
const { data: rdvRes, refresh, pending } = await useAsyncData("mes-rdv", () =>
  api.get("/patient/rendez-vous").catch(() => ({ data: [] }))
);

const rdvs = computed(() => (rdvRes.value?.data || []).map((r: any) => ({
  ...mapRendezVous(r),
  medecin: mapMedecin(r.medecin),
  specialiteNom: r.medecin?.specialites?.[0]?.nom || "",
})));

const filter = ref("tous");
const annulationEnCours = ref<string | null>(null);

const statusMap: Record<string, { label: string; class: string }> = {
  en_attente: { label: "En attente", class: "badge-pending" },
  confirme: { label: "Confirmé", class: "badge-confirmed" },
  reprogramme: { label: "Reprogrammé", class: "badge-pending" },
  refuse: { label: "Refusé", class: "badge-refused" },
  termine: { label: "Terminé", class: "badge-done" },
  annule: { label: "Annulé", class: "badge-refused" },
  absent: { label: "Absent", class: "badge-refused" },
};

const filtered = computed(() => (filter.value === "tous" ? rdvs.value : rdvs.value.filter((r: { statut: string; }) => r.statut === filter.value)));

async function annuler(id: string) {
  annulationEnCours.value = id;
  try {
    await api.post(`/patient/rendez-vous/${id}/annuler`);
    await refresh();
  } catch (e: any) {
    alert(e?.message || "Impossible d'annuler ce rendez-vous.");
  } finally {
    annulationEnCours.value = null;
  }
}

async function imprimer(id: string, ticket: string | null) {
  try {
    const blob: Blob = await $fetch(`/patient/rendez-vous/${id}/ticket`, {
      baseURL: useRuntimeConfig().public.apiBase as string,
      headers: { Authorization: `Bearer ${api.token.value}` },
      responseType: "blob",
    });
    const url = URL.createObjectURL(blob);
    const a = document.createElement("a");
    a.href = url;
    a.download = `ticket-${ticket || id}.pdf`;
    a.click();
    URL.revokeObjectURL(url);
  } catch (e: any) {
    alert(e?.message || "Ticket indisponible : le rendez-vous n'est pas encore confirmé.");
  }
}
</script>

<template>
  <DashboardShell role="patient" :nav="nav" title="Mes rendez-vous">
    <div class="flex flex-wrap gap-2 mb-6">
      <button v-for="f in [['tous','Tous'],['en_attente','En attente'],['confirme','Confirmés'],['termine','Terminés']]" :key="f[0]"
        @click="filter = f[0]"
        class="px-4 py-2 rounded-lg text-sm font-medium transition-colors"
        :class="filter === f[0] ? 'bg-azure-600 text-white' : 'bg-white border border-ink-200 text-ink-600 hover:border-azure-300'">
        {{ f[1] }}
      </button>
    </div>

    <div v-if="pending" class="card p-16 text-center text-sm text-ink-400">Chargement…</div>

    <div v-else class="space-y-4">
      <div v-for="r in filtered" :key="r.id" class="card p-5 flex flex-col sm:flex-row sm:items-center gap-5">
        <img :src="r.medecin.photo" class="w-16 h-16 rounded-xl object-cover shrink-0" alt="" />
        <div class="flex-1 min-w-0">
          <div class="flex items-center gap-2 flex-wrap">
            <p class="font-display font-semibold text-ink-950">Dr {{ r.medecin.prenom }} {{ r.medecin.nom }}</p>
            <span class="badge bg-ink-50 text-ink-500">{{ r.specialiteNom }}</span>
          </div>
          <p class="text-sm text-ink-500 mt-1">{{ r.motif || "Consultation" }}</p>
          <div class="flex items-center gap-4 mt-2 text-xs text-ink-400">
            <span class="flex items-center gap-1"><Icon name="calendar" class="w-3.5 h-3.5" />{{ new Date(r.date).toLocaleDateString('fr-FR', {day:'numeric', month:'short', year:'numeric'}) }}</span>
            <span class="flex items-center gap-1"><Icon name="clock" class="w-3.5 h-3.5" />{{ r.heure }}</span>
            <span v-if="r.ticket" class="font-mono">{{ r.ticket }}</span>
          </div>
        </div>
        <div class="flex items-center gap-3 shrink-0">
          <span class="badge" :class="statusMap[r.statut]?.class">{{ statusMap[r.statut]?.label || r.statut }}</span>
          <button v-if="r.ticket" class="btn-ghost !p-2.5" title="Imprimer le ticket" @click="imprimer(r.id, r.ticket)"><Icon name="printer" class="w-4.5 h-4.5" /></button>
          <button
            v-if="['en_attente','confirme'].includes(r.statut)"
            class="btn-ghost !p-2.5 hover:!text-clay"
            title="Annuler"
            :disabled="annulationEnCours === r.id"
            @click="annuler(r.id)"
          >
            <Icon name="x-circle" class="w-4.5 h-4.5" />
          </button>
        </div>
      </div>

      <div v-if="!filtered.length" class="card p-16 text-center">
        <Icon name="calendar" class="w-10 h-10 text-ink-300 mx-auto mb-4" />
        <p class="font-display font-semibold text-ink-800">Aucun rendez-vous ici</p>
        <p class="text-sm text-ink-500 mt-1">Changez de filtre ou réservez un nouveau rendez-vous.</p>
      </div>
    </div>
  </DashboardShell>
</template>
