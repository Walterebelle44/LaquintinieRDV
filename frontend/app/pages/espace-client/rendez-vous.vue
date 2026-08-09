<script setup lang="ts">
const nav = [
  { label: "Vue d'ensemble", to: "/espace-client", icon: "activity" },
  { label: "Mes rendez-vous", to: "/espace-client/rendez-vous", icon: "calendar" },
  { label: "Trouver un médecin", to: "/medecins", icon: "search" },
  { label: "Mon profil", to: "/espace-client/profil", icon: "user" },
];

const medecins = useMedecins();
const specialites = useSpecialites();
const rdvs = useRendezVous().filter((r) => r.patient === "Walter Djoko");
const filter = ref("tous");

function medecin(id: string) { return medecins.find((m) => m.id === id); }
function specialite(id: string) { return specialites.find((s) => s.id === medecin(id)?.specialiteId); }

const statusMap: Record<string, { label: string; class: string }> = {
  en_attente: { label: "En attente", class: "badge-pending" },
  confirme: { label: "Confirmé", class: "badge-confirmed" },
  refuse: { label: "Refusé", class: "badge-refused" },
  termine: { label: "Terminé", class: "badge-done" },
  annule: { label: "Annulé", class: "badge-refused" },
};

const filtered = computed(() => filter.value === "tous" ? rdvs : rdvs.filter((r) => r.statut === filter.value));
</script>

<template>
  <DashboardShell role="client" :nav="nav" title="Mes rendez-vous">
    <div class="flex flex-wrap gap-2 mb-6">
      <button v-for="f in [['tous','Tous'],['en_attente','En attente'],['confirme','Confirmés'],['termine','Terminés']]" :key="f[0]"
        @click="filter = f[0]"
        class="px-4 py-2 rounded-lg text-sm font-medium transition-colors"
        :class="filter === f[0] ? 'bg-azure-600 text-white' : 'bg-white border border-ink-200 text-ink-600 hover:border-azure-300'">
        {{ f[1] }}
      </button>
    </div>

    <div class="space-y-4">
      <div v-for="r in filtered" :key="r.id" class="card p-5 flex flex-col sm:flex-row sm:items-center gap-5">
        <img :src="medecin(r.medecinId)?.photo" class="w-16 h-16 rounded-xl object-cover shrink-0" alt="" />
        <div class="flex-1 min-w-0">
          <div class="flex items-center gap-2 flex-wrap">
            <p class="font-display font-semibold text-ink-950">Dr {{ medecin(r.medecinId)?.prenom }} {{ medecin(r.medecinId)?.nom }}</p>
            <span class="badge bg-ink-50 text-ink-500">{{ specialite(r.medecinId)?.nom }}</span>
          </div>
          <p class="text-sm text-ink-500 mt-1">{{ r.motif }}</p>
          <div class="flex items-center gap-4 mt-2 text-xs text-ink-400">
            <span class="flex items-center gap-1"><Icon name="calendar" class="w-3.5 h-3.5" />{{ new Date(r.date).toLocaleDateString('fr-FR', {day:'numeric', month:'short', year:'numeric'}) }}</span>
            <span class="flex items-center gap-1"><Icon name="clock" class="w-3.5 h-3.5" />{{ r.heure }}</span>
            <span class="font-mono">{{ r.ticket }}</span>
          </div>
        </div>
        <div class="flex items-center gap-3 shrink-0">
          <span class="badge" :class="statusMap[r.statut]?.class ?? 'badge-unknown'">{{ statusMap[r.statut]?.label ?? 'Inconnu' }}</span>
          <button class="btn-ghost !p-2.5" title="Imprimer le ticket"><Icon name="printer" class="w-4.5 h-4.5" /></button>
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
