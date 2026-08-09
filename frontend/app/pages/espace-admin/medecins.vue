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
const medecins = ref(useMedecins());
const showModal = ref(false);

function specialite(id: string) { return specialites.find((s) => s.id === id); }
function toggleBlock(m: any) { m.statut = m.statut === "actif" ? "bloque" : "actif"; }
</script>

<template>
  <DashboardShell role="admin" :nav="nav" title="Gestion des médecins">
    <div class="flex items-center justify-between mb-6">
      <p class="text-sm text-ink-500">{{ medecins.length }} médecins enregistrés</p>
      <button class="btn-primary !text-sm" @click="showModal = true"><Icon name="plus" class="w-4 h-4" />Ajouter un médecin</button>
    </div>

    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
      <div v-for="m in medecins" :key="m.id" class="card p-5">
        <div class="flex items-start gap-3">
          <img :src="m.photo" class="w-14 h-14 rounded-xl object-cover" alt="" />
          <div class="flex-1 min-w-0">
            <p class="font-display font-semibold text-ink-950 truncate">Dr {{ m.prenom }} {{ m.nom }}</p>
            <span class="badge bg-azure-50 text-azure-700 mt-1"><Icon :name="specialite(m.specialiteId)?.icone || 'heart'" class="w-3 h-3" />{{ specialite(m.specialiteId)?.nom }}</span>
          </div>
          <span class="badge shrink-0" :class="m.statut === 'actif' ? 'badge-confirmed' : 'badge-refused'">{{ m.statut === 'actif' ? 'Actif' : 'Bloqué' }}</span>
        </div>
        <div class="flex items-center gap-4 mt-4 text-xs text-ink-500">
          <span class="flex items-center gap-1"><Icon name="star" class="w-3.5 h-3.5 text-amber-400" />{{ m.note }}</span>
          <span class="flex items-center gap-1"><Icon name="activity" class="w-3.5 h-3.5" />{{ m.experience }} ans</span>
          <span class="flex items-center gap-1"><Icon name="trending-up" class="w-3.5 h-3.5" />{{ m.tarif.toLocaleString() }} F</span>
        </div>
        <div class="flex gap-2 mt-4 pt-4 border-t border-ink-100">
          <button class="btn-secondary !py-1.5 !text-xs flex-1"><Icon name="edit" class="w-3.5 h-3.5" />Modifier</button>
          <button @click="toggleBlock(m)" class="btn-secondary !py-1.5 !text-xs flex-1" :class="m.statut === 'actif' ? '!text-amber-600 !border-amber-200' : '!text-emerald-600 !border-emerald-200'">
            <Icon :name="m.statut === 'actif' ? 'lock' : 'check-circle'" class="w-3.5 h-3.5" />{{ m.statut === 'actif' ? 'Bloquer' : 'Débloquer' }}
          </button>
        </div>
      </div>
    </div>

    <div v-if="showModal" class="fixed inset-0 bg-ink-950/50 backdrop-blur-sm z-50 flex items-center justify-center p-4" @click.self="showModal = false">
      <div class="card w-full max-w-lg p-6 sm:p-7 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between mb-5">
          <h2 class="font-display font-semibold text-lg text-ink-950">Ajouter un médecin</h2>
          <button @click="showModal = false" class="text-ink-400 hover:text-ink-700"><Icon name="x" class="w-5 h-5" /></button>
        </div>
        <div class="space-y-4">
          <div class="grid grid-cols-2 gap-3">
            <div><label class="label">Prénom</label><input class="input" /></div>
            <div><label class="label">Nom</label><input class="input" /></div>
          </div>
          <div><label class="label">Email professionnel</label><input class="input" type="email" /></div>
          <div><label class="label">Téléphone</label><input class="input" type="tel" /></div>
          <div class="grid grid-cols-2 gap-3">
            <div><label class="label">Spécialité</label>
              <select class="input"><option v-for="s in specialites" :key="s.id">{{ s.nom }}</option></select>
            </div>
            <div><label class="label">N° d'ordre</label><input class="input" placeholder="OM-2026-XXXX" /></div>
          </div>
          <div><label class="label">Tarif de consultation (FCFA)</label><input class="input" type="number" /></div>
          <button class="btn-primary w-full" @click="showModal = false">Créer le compte médecin</button>
        </div>
      </div>
    </div>
  </DashboardShell>
</template>
