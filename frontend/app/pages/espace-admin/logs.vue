<script setup lang="ts">
const nav = [
  { label: "Vue d'ensemble", to: "/espace-admin", icon: "activity" },
  { label: "Utilisateurs", to: "/espace-admin/utilisateurs", icon: "users" },
  { label: "Médecins", to: "/espace-admin/medecins", icon: "stethoscope" },
  { label: "Spécialités", to: "/espace-admin/specialites", icon: "heart" },
  { label: "Rendez-vous", to: "/espace-admin/rendez-vous", icon: "calendar" },
  { label: "Journal des logs", to: "/espace-admin/logs", icon: "file-text" },
];

const logs = useLogs() ?? [];
const typeFilter = ref("Tous");

const typeConfig: Record<string, { label: string; icon: string; color: string }> = {
  securite: { label: "Sécurité", icon: "shield", color: "bg-azure-50 text-azure-700" },
  rdv: { label: "Rendez-vous", icon: "calendar", color: "bg-pulse-soft text-emerald-700" },
  connexion: { label: "Connexion", icon: "user", color: "bg-ink-100 text-ink-600" },
  config: { label: "Configuration", icon: "settings", color: "bg-amber-50 text-amber-700" },
  alerte: { label: "Alerte", icon: "alert-triangle", color: "bg-clay-soft text-clay" },
  export: { label: "Export", icon: "download", color: "bg-ink-100 text-ink-600" },
};

const defaultType = { label: "Inconnu", icon: "file-text", color: "bg-ink-100 text-ink-600" };

const getType = (t: string) => typeConfig[t] ?? defaultType;

const filtered = computed(() => (typeFilter.value === "Tous" ? logs : logs.filter((l) => typeConfig[l.type]?.label === typeFilter.value)));
</script>

<template>
  <DashboardShell role="admin" :nav="nav" title="Journal des logs">
    <div class="flex items-center justify-between mb-6">
      <p class="text-sm text-ink-500">Traçabilité complète des actions sur la plateforme</p>
      <div class="flex items-center gap-3">
        <select v-model="typeFilter" class="input !w-auto !py-2.5 text-sm">
          <option>Tous</option><option>Sécurité</option><option>Rendez-vous</option><option>Connexion</option><option>Configuration</option><option>Alerte</option><option>Export</option>
        </select>
        <button class="btn-secondary !text-sm"><Icon name="download" class="w-4 h-4" />Exporter</button>
      </div>
    </div>

    <div class="card divide-y divide-ink-100">
      <div v-for="l in filtered" :key="l.id" class="flex items-center gap-4 p-4 sm:p-5 hover:bg-ink-50/50">
        <span class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0" :class="getType(l.type).color">
          <Icon :name="getType(l.type).icon" class="w-4.5 h-4.5" />
        </span>
        <div class="flex-1 min-w-0">
          <p class="text-sm font-medium text-ink-800">{{ l.action }}</p>
          <p class="text-xs text-ink-400 mt-0.5">{{ l.auteur }}</p>
        </div>
        <span class="badge shrink-0" :class="getType(l.type).color">{{ getType(l.type).label }}</span>
        <span class="text-xs text-ink-400 font-mono w-36 text-right shrink-0">{{ l.date }}</span>
      </div>
      <div v-if="!filtered.length" class="p-12 text-center text-sm text-ink-500">Aucune entrée pour ce filtre.</div>
    </div>
  </DashboardShell>
</template>
