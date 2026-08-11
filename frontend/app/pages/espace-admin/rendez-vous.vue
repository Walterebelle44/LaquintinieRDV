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
const search = ref("");
const statutFilter = ref("Tous");

const { data: rdvRes, refresh, pending } = await useAsyncData("admin-tous-rdv", () =>
  api.get("/admin/rendez-vous?per_page=50").catch(() => ({ data: [] }))
);

const rdvs = computed(() => (rdvRes.value?.data || []).map((r: any) => ({
  ...mapRendezVous(r),
  medecinNom: r.medecin?.user ? `Dr ${r.medecin.user.prenom} ${r.medecin.user.nom}` : "",
})));

const statusMap: Record<string, { label: string; class: string }> = {
  en_attente: { label: "En attente", class: "badge-pending" },
  confirme: { label: "Confirmé", class: "badge-confirmed" },
  refuse: { label: "Refusé", class: "badge-refused" },
  termine: { label: "Terminé", class: "badge-done" },
  annule: { label: "Annulé", class: "badge-refused" },
  reprogramme: { label: "Reprogrammé", class: "badge-pending" },
  absent: { label: "Absent", class: "badge-refused" },
};

const filtered = computed(() =>
  rdvs.value.filter(
    (r: any) =>
      (statutFilter.value === "Tous" || statusMap[r.statut]?.label === statutFilter.value) &&
      (r.patient.toLowerCase().includes(search.value.toLowerCase()) || (r.ticket || "").toLowerCase().includes(search.value.toLowerCase()))
  )
);

async function exporterCsv() {
  try {
    const blob: Blob = await $fetch("/admin/export/rendez-vous", {
      baseURL: useRuntimeConfig().public.apiBase as string | undefined,
      headers: { Authorization: `Bearer ${api.token.value}` },
      responseType: "blob",
    });
    const url = URL.createObjectURL(blob);
    const a = document.createElement("a");
    a.href = url;
    a.download = `rendez-vous-${new Date().toISOString().slice(0, 10)}.csv`;
    a.click();
    URL.revokeObjectURL(url);
  } catch (e: any) {
    alert(e?.message || "Export impossible.");
  }
}
</script>

<template>
  <DashboardShell role="admin" :nav="nav" title="Tous les rendez-vous">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
      <div class="flex items-center gap-2 p-1.5 bg-white border border-ink-200 rounded-xl max-w-sm flex-1">
        <Icon name="search" class="w-4.5 h-4.5 text-ink-400 ml-2" />
        <input v-model="search" placeholder="Patient ou n° de ticket…" class="w-full bg-transparent py-1.5 outline-none text-sm" />
      </div>
      <div class="flex items-center gap-3">
        <select v-model="statutFilter" class="input !w-auto !py-2.5 text-sm">
          <option>Tous</option><option>En attente</option><option>Confirmé</option><option>Terminé</option><option>Refusé</option>
        </select>
        <button class="btn-secondary !text-sm" @click="exporterCsv"><Icon name="download" class="w-4 h-4" />Exporter</button>
      </div>
    </div>

    <div v-if="pending" class="card p-16 text-center text-sm text-ink-400">Chargement…</div>

    <div v-else class="card overflow-hidden">
      <table class="w-full text-sm">
        <thead class="bg-ink-50/80">
          <tr class="text-left text-xs text-ink-500">
            <th class="px-5 py-3.5 font-medium">Ticket</th>
            <th class="px-5 py-3.5 font-medium">Patient</th>
            <th class="px-5 py-3.5 font-medium">Médecin</th>
            <th class="px-5 py-3.5 font-medium">Date & heure</th>
            <th class="px-5 py-3.5 font-medium">Statut</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-ink-100">
          <tr v-for="r in filtered" :key="r.id" class="hover:bg-ink-50/50">
            <td class="px-5 py-3.5 font-mono text-xs text-azure-700">{{ r.ticket || "—" }}</td>
            <td class="px-5 py-3.5 font-medium text-ink-800">{{ r.patient }}</td>
            <td class="px-5 py-3.5 text-ink-600">{{ r.medecinNom }}</td>
            <td class="px-5 py-3.5 text-ink-500">{{ new Date(r.date).toLocaleDateString('fr-FR', {day:'numeric', month:'short'}) }} · {{ r.heure }}</td>
            <td class="px-5 py-3.5"><span class="badge" :class="statusMap[r.statut]?.class">{{ statusMap[r.statut]?.label || r.statut }}</span></td>
          </tr>
        </tbody>
      </table>
      <div v-if="!filtered.length" class="p-12 text-center text-sm text-ink-500">Aucun rendez-vous ne correspond à ces critères.</div>
    </div>
  </DashboardShell>
</template>
