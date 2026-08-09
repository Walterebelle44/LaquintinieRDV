<script setup lang="ts">
const nav = [
  { label: "Vue d'ensemble", to: "/espace-medecin", icon: "activity" },
  { label: "Demandes de RDV", to: "/espace-medecin/demandes", icon: "bell" },
  { label: "Mon planning", to: "/espace-medecin/planning", icon: "calendar" },
  { label: "Mon profil", to: "/espace-medecin/profil", icon: "user" },
];

const rdvs = ref(useRendezVous().filter((r) => r.medecinId === "m1"));
const rescheduling = ref<string | null>(null);
const newTime = ref("");

function accept(id: string) {
  const r = rdvs.value.find((x) => x.id === id);
  if (r) r.statut = "confirme";
}
function refuse(id: string) {
  const r = rdvs.value.find((x) => x.id === id);
  if (r) r.statut = "refuse";
}
function openReschedule(id: string) {
  rescheduling.value = rescheduling.value === id ? null : id;
}
function applyReschedule(id: string) {
  const r = rdvs.value.find((x) => x.id === id);
  if (r && newTime.value) {
    r.heure = newTime.value;
    r.statut = "confirme";
  }
  rescheduling.value = null;
  newTime.value = "";
}

const enAttente = computed(() => rdvs.value.filter((r) => r.statut === "en_attente"));
const traitees = computed(() => rdvs.value.filter((r) => r.statut !== "en_attente"));
</script>

<template>
  <DashboardShell role="medecin" :nav="nav" title="Demandes de rendez-vous">
    <div class="mb-10">
      <h2 class="font-display font-semibold text-ink-900 mb-4 flex items-center gap-2">
        <span class="w-2 h-2 rounded-full bg-amber-500"></span>
        En attente de réponse ({{ enAttente.length }})
      </h2>

      <div v-if="enAttente.length" class="space-y-4">
        <div v-for="r in enAttente" :key="r.id" class="card p-5">
          <div class="flex flex-col sm:flex-row sm:items-center gap-4">
            <div class="w-12 h-12 rounded-full bg-azure-100 text-azure-700 flex items-center justify-center font-display font-semibold shrink-0">
              {{ r.patient.split(' ').map(n=>n[0]).join('').slice(0,2) }}
            </div>
            <div class="flex-1 min-w-0">
              <p class="font-display font-semibold text-ink-950">{{ r.patient }}</p>
              <p class="text-sm text-ink-500">{{ r.motif }}</p>
              <div class="flex items-center gap-4 mt-1.5 text-xs text-ink-400">
                <span class="flex items-center gap-1"><Icon name="calendar" class="w-3.5 h-3.5" />{{ new Date(r.date).toLocaleDateString('fr-FR', {day:'numeric', month:'long'}) }}</span>
                <span class="flex items-center gap-1"><Icon name="clock" class="w-3.5 h-3.5" />{{ r.heure }}</span>
                <span class="font-mono">{{ r.ticket }}</span>
              </div>
            </div>
            <div class="flex gap-2 shrink-0">
              <button @click="accept(r.id)" class="btn-primary !py-2 !px-3.5 !text-sm"><Icon name="check" class="w-4 h-4" />Accepter</button>
              <button @click="openReschedule(r.id)" class="btn-secondary !py-2 !px-3.5 !text-sm"><Icon name="clock" class="w-4 h-4" />Reprogrammer</button>
              <button @click="refuse(r.id)" class="btn-secondary !py-2 !px-3 !text-sm !text-clay !border-clay/20 hover:!bg-clay/5"><Icon name="x" class="w-4 h-4" /></button>
            </div>
          </div>

          <div v-if="rescheduling === r.id" class="mt-4 pt-4 border-t border-ink-100 flex items-center gap-3">
            <input v-model="newTime" type="time" class="input !w-40" />
            <button @click="applyReschedule(r.id)" class="btn-primary !py-2 !text-sm">Proposer ce créneau</button>
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
            {{ r.patient.split(' ').map(n=>n[0]).join('').slice(0,2) }}
          </div>
          <div class="flex-1 min-w-0">
            <p class="font-medium text-ink-800 text-sm">{{ r.patient }}</p>
            <p class="text-xs text-ink-400">{{ new Date(r.date).toLocaleDateString('fr-FR', {day:'numeric', month:'short'}) }} à {{ r.heure }}</p>
          </div>
          <span class="badge" :class="{ 'badge-confirmed': r.statut === 'confirme', 'badge-refused': r.statut === 'refuse', 'badge-done': r.statut === 'termine' }">
            {{ { confirme: 'Confirmé', refuse: 'Refusé', termine: 'Terminé', annule: 'Annulé', en_attente: 'En attente' }[r.statut] || r.statut }}
          </span>
        </div>
      </div>
    </div>
  </DashboardShell>
</template>
