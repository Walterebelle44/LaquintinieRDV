<script setup lang="ts">
const nav = [
  { label: "Vue d'ensemble", to: "/espace-admin", icon: "activity" },
  { label: "Utilisateurs", to: "/espace-admin/utilisateurs", icon: "users" },
  { label: "Médecins", to: "/espace-admin/medecins", icon: "stethoscope" },
  { label: "Spécialités", to: "/espace-admin/specialites", icon: "heart" },
  { label: "Rendez-vous", to: "/espace-admin/rendez-vous", icon: "calendar" },
  { label: "Journal des logs", to: "/espace-admin/logs", icon: "file-text" },
];

const specialites = useSpecialites();
const medecins = useMedecins();
const rdvs = useRendezVous();

const repartition = computed(() =>
  specialites.map((s) => ({
    ...s,
    count: medecins.filter((m) => m.specialiteId === s.id).length,
  }))
);
const maxCount = computed(() => Math.max(...repartition.value.map((r) => r.nbMedecins)));
</script>

<template>
  <DashboardShell role="admin" :nav="nav" title="Tableau de bord">
    <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
      <StatCard label="Utilisateurs totaux" value="1 284" icon="users" trend="+42 cette semaine" trend-up />
      <StatCard label="Médecins actifs" value="52" icon="stethoscope" color="bg-pulse-soft text-emerald-600" />
      <StatCard label="Rendez-vous ce mois" value="3 412" icon="calendar" color="bg-amber-50 text-amber-600" />
      <StatCard label="Taux d'annulation" value="6.2%" icon="alert-triangle" trend="-1.1% vs mois dernier" trend-up color="bg-clay-soft text-clay" />
    </div>

    <div class="grid lg:grid-cols-[1.4fr_1fr] gap-6 mb-8">
      <div class="card p-6 sm:p-7">
        <div class="flex items-center justify-between mb-6">
          <h2 class="font-display font-semibold text-ink-900">Rendez-vous par spécialité</h2>
          <select class="input !w-auto !py-1.5 !text-xs"><option>30 derniers jours</option></select>
        </div>
        <div class="space-y-4">
          <div v-for="r in repartition" :key="r.id" class="flex items-center gap-4">
            <span class="w-9 h-9 rounded-lg bg-azure-50 text-azure-600 flex items-center justify-center shrink-0"><Icon :name="r.icone" class="w-4.5 h-4.5" /></span>
            <div class="flex-1">
              <div class="flex items-center justify-between text-sm mb-1">
                <span class="font-medium text-ink-800">{{ r.nom }}</span>
                <span class="text-ink-400">{{ r.nbMedecins * 23 }} RDV</span>
              </div>
              <div class="h-2 rounded-full bg-ink-100 overflow-hidden">
                <div class="h-full bg-azure-500 rounded-full" :style="{ width: `${(r.nbMedecins / maxCount) * 100}%` }" />
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="card p-6 sm:p-7">
        <h2 class="font-display font-semibold text-ink-900 mb-6">Statuts des rendez-vous</h2>
        <div class="space-y-4">
          <div v-for="s in [
            { label: 'Confirmés', value: 68, color: 'bg-emerald-500' },
            { label: 'En attente', value: 14, color: 'bg-amber-500' },
            { label: 'Terminés', value: 12, color: 'bg-ink-400' },
            { label: 'Refusés / annulés', value: 6, color: 'bg-clay' },
          ]" :key="s.label">
            <div class="flex items-center justify-between text-sm mb-1.5">
              <span class="text-ink-600">{{ s.label }}</span>
              <span class="font-semibold text-ink-900">{{ s.value }}%</span>
            </div>
            <div class="h-2 rounded-full bg-ink-100 overflow-hidden">
              <div class="h-full rounded-full" :class="s.color" :style="{ width: s.value + '%' }" />
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="card p-6 sm:p-7">
      <div class="flex items-center justify-between mb-5">
        <h2 class="font-display font-semibold text-ink-900">Derniers rendez-vous enregistrés</h2>
        <NuxtLink to="/espace-admin/rendez-vous" class="text-sm text-azure-600 font-medium hover:text-azure-700">Tout voir</NuxtLink>
      </div>
      <div class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead>
            <tr class="text-left text-xs text-ink-400 border-b border-ink-100">
              <th class="pb-3 font-medium">Ticket</th>
              <th class="pb-3 font-medium">Patient</th>
              <th class="pb-3 font-medium">Médecin</th>
              <th class="pb-3 font-medium">Date</th>
              <th class="pb-3 font-medium">Statut</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-ink-50">
            <tr v-for="r in rdvs" :key="r.id">
              <td class="py-3 font-mono text-xs text-ink-500">{{ r.ticket }}</td>
              <td class="py-3 font-medium text-ink-800">{{ r.patient }}</td>
              <td class="py-3 text-ink-600">{{ medecins.find(m=>m.id===r.medecinId)?.prenom }} {{ medecins.find(m=>m.id===r.medecinId)?.nom }}</td>
              <td class="py-3 text-ink-500">{{ new Date(r.date).toLocaleDateString('fr-FR', {day:'numeric', month:'short'}) }}</td>
              <td class="py-3">
                <span class="badge" :class="{ 'badge-confirmed': r.statut === 'confirme', 'badge-pending': r.statut === 'en_attente', 'badge-refused': r.statut === 'refuse', 'badge-done': r.statut === 'termine' }">
                  {{ { en_attente: 'En attente', confirme: 'Confirmé', refuse: 'Refusé', termine: 'Terminé', annule: 'Annulé' }[r.statut] }}
                </span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </DashboardShell>
</template>
