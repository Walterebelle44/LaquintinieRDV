<script setup lang="ts">
import { useApi } from '~/composables/useApi';
import { mapRendezVous } from '~/composables/useMappers';

definePageMeta({ layout: "blank" as any, roles: ["admin"] });
const nav = [
  { label: "Vue d'ensemble", to: "/espace-admin", icon: "activity" },
  { label: "Utilisateurs", to: "/espace-admin/utilisateurs", icon: "users" },
  { label: "Médecins", to: "/espace-admin/medecins", icon: "stethoscope" },
  { label: "Spécialités", to: "/espace-admin/specialites", icon: "heart" },
  { label: "Rendez-vous", to: "/espace-admin/rendez-vous", icon: "calendar" },
  { label: "Journal des logs", to: "/espace-admin/logs", icon: "file-text" },
];

const api = useApi();

const { data: dashRes } = await useAsyncData("admin-dashboard", () =>
  api.get("/admin/tableau-de-bord").catch(() => ({ data: null }))
);
const { data: rdvRes } = await useAsyncData("admin-derniers-rdv", () =>
  api.get("/admin/rendez-vous?per_page=8").catch(() => ({ data: [] }))
);

const stats = computed(() => dashRes.value?.data);
const derniersRdv = computed(() => (rdvRes.value?.data || []).map((r: any) => ({
  ...mapRendezVous(r),
  medecinNom: r.medecin?.user ? `Dr ${r.medecin.user.prenom} ${r.medecin.user.nom}` : "",
})));

const repartitionSpecialites = computed(() => stats.value?.repartition_specialites || []);
const maxRdvSpecialite = computed(() => Math.max(1, ...repartitionSpecialites.value.map((r: any) => r.rdv_count || 0)));

const statusLabel: Record<string, string> = {
  en_attente: "En attente", confirme: "Confirmé", refuse: "Refusé", termine: "Terminé", annule: "Annulé", reprogramme: "Reprogrammé", absent: "Absent",
};
const statusClass: Record<string, string> = {
  en_attente: "badge-pending", confirme: "badge-confirmed", refuse: "badge-refused", termine: "badge-done", annule: "badge-refused",
};
</script>

<template>
  <DashboardShell role="admin" :nav="nav" title="Tableau de bord">
    <div v-if="!stats" class="card p-16 text-center text-sm text-ink-400">Chargement des statistiques…</div>

    <template v-else>
      <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <StatCard label="Utilisateurs totaux" :value="String(stats.total_utilisateurs)" icon="users" />
        <StatCard label="Médecins actifs" :value="String(stats.total_medecins_actifs)" icon="stethoscope" color="bg-pulse-soft text-emerald-600" />
        <StatCard label="Rendez-vous ce mois" :value="String(stats.rdv_ce_mois)" icon="calendar" color="bg-amber-50 text-amber-600" />
        <StatCard label="Taux d'annulation" :value="`${stats.taux_annulation}%`" icon="alert-triangle" color="bg-clay-soft text-clay" />
      </div>

      <div class="grid lg:grid-cols-[1.4fr_1fr] gap-6 mb-8">
        <div class="card p-6 sm:p-7">
          <h2 class="font-display font-semibold text-ink-900 mb-6">Rendez-vous par spécialité</h2>
          <div class="space-y-4">
            <div v-for="r in repartitionSpecialites" :key="r.id" class="flex items-center gap-4">
              <div class="flex-1">
                <div class="flex items-center justify-between text-sm mb-1">
                  <span class="font-medium text-ink-800">{{ r.nom }}</span>
                  <span class="text-ink-400">{{ r.rdv_count || 0 }} RDV</span>
                </div>
                <div class="h-2 rounded-full bg-ink-100 overflow-hidden">
                  <div class="h-full bg-azure-500 rounded-full" :style="{ width: `${((r.rdv_count || 0) / maxRdvSpecialite) * 100}%` }" />
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="card p-6 sm:p-7">
          <h2 class="font-display font-semibold text-ink-900 mb-6">Statuts des rendez-vous</h2>
          <div class="space-y-4">
            <div v-for="(count, statut) in stats.repartition_statuts" :key="statut">
              <div class="flex items-center justify-between text-sm mb-1.5">
                <span class="text-ink-600">{{ statusLabel[statut] || statut }}</span>
                <span class="font-semibold text-ink-900">{{ count }}</span>
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
              <tr v-for="r in derniersRdv" :key="r.id">
                <td class="py-3 font-mono text-xs text-ink-500">{{ r.ticket || "—" }}</td>
                <td class="py-3 font-medium text-ink-800">{{ r.patient }}</td>
                <td class="py-3 text-ink-600">{{ r.medecinNom }}</td>
                <td class="py-3 text-ink-500">{{ new Date(r.date).toLocaleDateString('fr-FR', {day:'numeric', month:'short'}) }}</td>
                <td class="py-3"><span class="badge" :class="statusClass[r.statut]">{{ statusLabel[r.statut] || r.statut }}</span></td>
              </tr>
            </tbody>
          </table>
          <div v-if="!derniersRdv.length" class="text-center text-sm text-ink-400 py-8">Aucun rendez-vous enregistré.</div>
        </div>
      </div>
    </template>
  </DashboardShell>
</template>
