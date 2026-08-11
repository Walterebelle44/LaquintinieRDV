<script setup lang="ts">
import { useApi } from '~/composables/useApi';

definePageMeta({ layout: "blank" as any, roles: ["admin"] });

interface LogEntry {
  id: number;
  action: string;
  type: string;
  auteur_libelle?: string | null;
  created_at: string;
}

const nav = [
  { label: "Vue d'ensemble", to: "/espace-admin", icon: "activity" },
  { label: "Utilisateurs", to: "/espace-admin/utilisateurs", icon: "users" },
  { label: "Médecins", to: "/espace-admin/medecins", icon: "stethoscope" },
  { label: "Spécialités", to: "/espace-admin/specialites", icon: "heart" },
  { label: "Rendez-vous", to: "/espace-admin/rendez-vous", icon: "calendar" },
  { label: "Journal des logs", to: "/espace-admin/logs", icon: "file-text" },
];

const api = useApi();
const typeFilter = ref("Tous");

const typeConfig: Record<string, { label: string; icon: string; color: string }> = {
  securite: { label: "Sécurité", icon: "shield", color: "bg-azure-50 text-azure-700" },
  rdv: { label: "Rendez-vous", icon: "calendar", color: "bg-pulse-soft text-emerald-700" },
  connexion: { label: "Connexion", icon: "user", color: "bg-ink-100 text-ink-600" },
  config: { label: "Configuration", icon: "settings", color: "bg-amber-50 text-amber-700" },
  alerte: { label: "Alerte", icon: "alert-triangle", color: "bg-clay-soft text-clay" },
  export: { label: "Export", icon: "download", color: "bg-ink-100 text-ink-600" },
  info: { label: "Info", icon: "activity", color: "bg-ink-100 text-ink-600" },
};

function mapTypeToApi(label: string): string | undefined {
  return Object.entries(typeConfig).find(([, v]) => v.label === label)?.[0];
}

const { data: logsRes, pending } = await useAsyncData<{ data: LogEntry[] }>(
  "admin-logs",
  async () => {
    try {
      return await api.get<{ data: LogEntry[] }>("/admin/logs", {
        query: { type: typeFilter.value === "Tous" ? undefined : mapTypeToApi(typeFilter.value) },
      });
    } catch {
      return { data: [] as LogEntry[] };
    }
  },
  { watch: [typeFilter] }
);

const logs = computed<LogEntry[]>(() => logsRes.value?.data ?? []);

function getTypeCfg(type?: string): { label: string; icon: string; color: string } {
  if (!type) {
    return typeConfig.info;
  }
  return (typeConfig[type] ?? typeConfig.info) as { label: string; icon: string; color: string };
}
</script>

<template>
  <DashboardShell role="admin" :nav="nav" title="Journal des logs">
    <div class="flex items-center justify-between mb-6">
      <p class="text-sm text-ink-500">Traçabilité complète des actions sur la plateforme</p>
      <select v-model="typeFilter" class="input !w-auto !py-2.5 text-sm">
        <option>Tous</option><option>Sécurité</option><option>Rendez-vous</option><option>Connexion</option><option>Configuration</option><option>Alerte</option><option>Export</option>
      </select>
    </div>

    <div v-if="pending" class="card p-16 text-center text-sm text-ink-400">Chargement…</div>

    <div v-else class="card divide-y divide-ink-100">
      <div v-for="l in logs" :key="l.id" class="flex items-center gap-4 p-4 sm:p-5 hover:bg-ink-50/50">
        <span class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0" :class="getTypeCfg(l.type).color">
          <Icon :name="getTypeCfg(l.type).icon" class="w-4.5 h-4.5" />
        </span>
        <div class="flex-1 min-w-0">
          <p class="text-sm font-medium text-ink-800">{{ l.action }}</p>
          <p class="text-xs text-ink-400 mt-0.5">{{ l.auteur_libelle || "système" }}</p>
        </div>
        <span class="badge shrink-0" :class="getTypeCfg(l.type).color">{{ getTypeCfg(l.type).label }}</span>
        <span class="text-xs text-ink-400 font-mono w-40 text-right shrink-0">{{ new Date(l.created_at).toLocaleString('fr-FR') }}</span>
      </div>
      <div v-if="!logs.length" class="p-12 text-center text-sm text-ink-500">Aucune entrée pour ce filtre.</div>
    </div>
  </DashboardShell>
</template>