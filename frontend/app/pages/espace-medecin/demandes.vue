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
const { data: rdvRes, refresh, pending } = await useAsyncData("medecin-demandes", () =>
  api.get("/medecin/rendez-vous").catch(() => ({ data: [] }))
);

const rdvs = computed(() => (rdvRes.value?.data || []).map(mapRendezVous));
const enAttente = computed(() => rdvs.value.filter((r: { statut: string; }) => r.statut === "en_attente"));
const traitees = computed(() => rdvs.value.filter((r: { statut: string; }) => r.statut !== "en_attente"));

const rescheduling = ref<string | null>(null);
const newDate = ref("");
const newTime = ref("");
const actionEnCours = ref<string | null>(null);

async function accepter(id: string) {
  actionEnCours.value = id;
  try {
    await api.post(`/medecin/rendez-vous/${id}/repondre`, { action: "accepter" });
    await refresh();
  } catch (e: any) {
    alert(e?.message || "Action impossible.");
  } finally {
    actionEnCours.value = null;
  }
}

async function refuser(id: string) {
  const motif = prompt("Motif du refus (visible par le patient) :");
  if (motif === null) return;
  actionEnCours.value = id;
  try {
    await api.post(`/medecin/rendez-vous/${id}/repondre`, { action: "refuser", motif_refus: motif || "Non précisé" });
    await refresh();
  } catch (e: any) {
    alert(e?.message || "Action impossible.");
  } finally {
    actionEnCours.value = null;
  }
}

function openReschedule(id: string) {
  rescheduling.value = rescheduling.value === id ? null : id;
  newDate.value = "";
  newTime.value = "";
}

async function applyReschedule(id: string) {
  if (!newDate.value || !newTime.value) return;
  actionEnCours.value = id;
  try {
    await api.post(`/medecin/rendez-vous/${id}/repondre`, {
      action: "reprogrammer",
      nouvelle_date: newDate.value,
      nouvelle_heure: newTime.value,
    });
    rescheduling.value = null;
    await refresh();
  } catch (e: any) {
    alert(e?.message || "Action impossible.");
  } finally {
    actionEnCours.value = null;
  }
}

const statusLabel: Record<string, string> = {
  confirme: "Confirmé", refuse: "Refusé", termine: "Terminé", annule: "Annulé", reprogramme: "Reprogrammé", absent: "Absent",
};
</script>

<template>
  <DashboardShell role="medecin" :nav="nav" title="Demandes de rendez-vous">
    <div v-if="pending" class="card p-16 text-center text-sm text-ink-400">Chargement…</div>

    <template v-else>
      <div class="mb-10">
        <h2 class="font-display font-semibold text-ink-900 mb-4 flex items-center gap-2">
          <span class="w-2 h-2 rounded-full bg-amber-500"></span>
          En attente de réponse ({{ enAttente.length }})
        </h2>

        <div v-if="enAttente.length" class="space-y-4">
          <div v-for="r in enAttente" :key="r.id" class="card p-5">
            <div class="flex flex-col sm:flex-row sm:items-center gap-4">
              <div class="w-12 h-12 rounded-full bg-azure-100 text-azure-700 flex items-center justify-center font-display font-semibold shrink-0">
                {{ r.patient.split(' ').map((n: any[])=>n[0]).join('').slice(0,2) }}
              </div>
              <div class="flex-1 min-w-0">
                <p class="font-display font-semibold text-ink-950">{{ r.patient }}</p>
                <p class="text-sm text-ink-500">{{ r.motif || "Consultation" }}</p>
                <div class="flex items-center gap-4 mt-1.5 text-xs text-ink-400">
                  <span class="flex items-center gap-1"><Icon name="calendar" class="w-3.5 h-3.5" />{{ new Date(r.date).toLocaleDateString('fr-FR', {day:'numeric', month:'long'}) }}</span>
                  <span class="flex items-center gap-1"><Icon name="clock" class="w-3.5 h-3.5" />{{ r.heure }}</span>
                  <span v-if="r.ticket" class="font-mono">{{ r.ticket }}</span>
                </div>
              </div>
              <div class="flex gap-2 shrink-0">
                <button :disabled="actionEnCours === r.id" @click="accepter(r.id)" class="btn-primary !py-2 !px-3.5 !text-sm"><Icon name="check" class="w-4 h-4" />Accepter</button>
                <button :disabled="actionEnCours === r.id" @click="openReschedule(r.id)" class="btn-secondary !py-2 !px-3.5 !text-sm"><Icon name="clock" class="w-4 h-4" />Reprogrammer</button>
                <button :disabled="actionEnCours === r.id" @click="refuser(r.id)" class="btn-secondary !py-2 !px-3 !text-sm !text-clay !border-clay/20 hover:!bg-clay/5"><Icon name="x" class="w-4 h-4" /></button>
              </div>
            </div>

            <div v-if="rescheduling === r.id" class="mt-4 pt-4 border-t border-ink-100 flex flex-wrap items-center gap-3">
              <input v-model="newDate" type="date" class="input !w-44" />
              <input v-model="newTime" type="time" class="input !w-32" />
              <button :disabled="!newDate || !newTime || actionEnCours === r.id" @click="applyReschedule(r.id)" class="btn-primary !py-2 !text-sm">Proposer ce créneau</button>
              <span class="text-xs text-ink-400">Le patient sera notifié du nouveau créneau.</span>
            </div>
          </div>
        </div>
        <div v-else class="card p-10 text-center text-sm text-ink-500">Aucune demande en attente. 🎉</div>
      </div>

      <div>
        <h2 class="font-display font-semibold text-ink-900 mb-4">Historique des réponses</h2>
        <div class="card divide-y divide-ink-100">
          <div v-for="r in traitees" :key="r.id" class="flex items-center gap-4 p-4">
            <div class="w-10 h-10 rounded-full bg-ink-100 text-ink-600 flex items-center justify-center font-display font-semibold text-sm shrink-0">
              {{ r.patient.split(' ').map((n: any[])=>n[0]).join('').slice(0,2) }}
            </div>
            <div class="flex-1 min-w-0">
              <p class="font-medium text-ink-800 text-sm">{{ r.patient }}</p>
              <p class="text-xs text-ink-400">{{ new Date(r.date).toLocaleDateString('fr-FR', {day:'numeric', month:'short'}) }} à {{ r.heure }}</p>
            </div>
            <span class="badge" :class="{ 'badge-confirmed': r.statut === 'confirme', 'badge-refused': r.statut === 'refuse', 'badge-done': r.statut === 'termine' }">
              {{ statusLabel[r.statut] || r.statut }}
            </span>
          </div>
          <div v-if="!traitees.length" class="p-10 text-center text-sm text-ink-400">Aucun historique pour le moment.</div>
        </div>
      </div>
    </template>
  </DashboardShell>
</template>
