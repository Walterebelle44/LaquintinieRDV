<script setup lang="ts">
const nav = [
  { label: "Vue d'ensemble", to: "/espace-medecin", icon: "activity" },
  { label: "Demandes de RDV", to: "/espace-medecin/demandes", icon: "bell" },
  { label: "Mon planning", to: "/espace-medecin/planning", icon: "calendar" },
  { label: "Mon profil", to: "/espace-medecin/profil", icon: "user" },
];

const jours = ["Lundi", "Mardi", "Mercredi", "Jeudi", "Vendredi", "Samedi"];
const heures = ["08:00", "09:00", "10:00", "11:00", "14:00", "15:00", "16:00", "17:00"];

// simple grid occupancy simulation
const occ: Record<string, "libre" | "occupe" | "bloque"> = {
  "Lundi-09:00": "occupe", "Lundi-11:00": "occupe", "Lundi-15:00": "occupe",
  "Mardi-08:00": "occupe", "Mardi-10:00": "bloque", "Mardi-16:00": "occupe",
  "Mercredi-09:00": "occupe", "Mercredi-14:00": "occupe",
  "Jeudi-11:00": "occupe", "Jeudi-15:00": "bloque", "Jeudi-16:00": "occupe",
  "Vendredi-08:00": "occupe", "Vendredi-09:00": "occupe", "Vendredi-17:00": "occupe",
};
function status(j: string, h: string) { return occ[`${j}-${h}`] || "libre"; }

const disponibilites = ref([
  { jour: "Lundi", debut: "08:00", fin: "15:00" },
  { jour: "Mardi", debut: "08:00", fin: "17:30" },
  { jour: "Jeudi", debut: "08:00", fin: "17:30" },
  { jour: "Vendredi", debut: "08:00", fin: "13:00" },
]);
</script>

<template>
  <DashboardShell role="medecin" :nav="nav" title="Mon planning">
    <div class="grid lg:grid-cols-[1fr_320px] gap-6">
      <div class="card p-6 overflow-x-auto">
        <div class="flex items-center justify-between mb-5">
          <h2 class="font-display font-semibold text-ink-900">Semaine du 04 au 09 août 2026</h2>
          <div class="flex items-center gap-4 text-xs text-ink-500">
            <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-sm bg-azure-500"></span>Occupé</span>
            <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-sm bg-clay/70"></span>Bloqué</span>
            <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-sm bg-ink-100"></span>Libre</span>
          </div>
        </div>
        <table class="w-full min-w-[560px] border-separate border-spacing-1.5">
          <thead>
            <tr>
              <th class="text-xs text-ink-400 font-medium w-16"></th>
              <th v-for="j in jours" :key="j" class="text-xs text-ink-500 font-semibold pb-2">{{ j.slice(0,3) }}</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="h in heures" :key="h">
              <td class="text-xs text-ink-400 font-mono pr-2">{{ h }}</td>
              <td v-for="j in jours" :key="j+h">
                <div
                  class="h-9 rounded-lg"
                  :class="{
                    'bg-azure-500': status(j,h) === 'occupe',
                    'bg-clay/70': status(j,h) === 'bloque',
                    'bg-ink-100 hover:bg-ink-200 cursor-pointer transition-colors': status(j,h) === 'libre',
                  }"
                  :title="status(j,h)"
                />
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div class="space-y-6">
        <div class="card p-6">
          <h2 class="font-display font-semibold text-ink-900 mb-4">Disponibilités récurrentes</h2>
          <div class="space-y-2.5">
            <div v-for="d in disponibilites" :key="d.jour" class="flex items-center justify-between p-3 rounded-xl bg-ink-50 text-sm">
              <span class="font-medium text-ink-800">{{ d.jour }}</span>
              <span class="text-ink-500 font-mono text-xs">{{ d.debut }} – {{ d.fin }}</span>
            </div>
          </div>
          <button class="btn-secondary w-full mt-4 !text-sm"><Icon name="edit" class="w-4 h-4" />Modifier mes disponibilités</button>
        </div>

        <div class="card p-6">
          <h2 class="font-display font-semibold text-ink-900 mb-3">Bloquer un créneau</h2>
          <p class="text-sm text-ink-500 mb-4">Urgence ou absence imprévue ? Bloquez rapidement une plage horaire.</p>
          <div class="space-y-3">
            <input type="date" class="input" />
            <div class="grid grid-cols-2 gap-3">
              <input type="time" class="input" />
              <input type="time" class="input" />
            </div>
            <button class="btn-primary w-full !text-sm">Bloquer ce créneau</button>
          </div>
        </div>
      </div>
    </div>
  </DashboardShell>
</template>
