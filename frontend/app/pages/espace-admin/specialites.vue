<script setup lang="ts">
const nav = [
  { label: "Vue d'ensemble", to: "/espace-admin", icon: "activity" },
  { label: "Utilisateurs", to: "/espace-admin/utilisateurs", icon: "users" },
  { label: "Médecins", to: "/espace-admin/medecins", icon: "stethoscope" },
  { label: "Spécialités", to: "/espace-admin/specialites", icon: "heart" },
  { label: "Rendez-vous", to: "/espace-admin/rendez-vous", icon: "calendar" },
  { label: "Journal des logs", to: "/espace-admin/logs", icon: "file-text" },
];

const specialites = ref(useSpecialites());
const showModal = ref(false);
const newSpec = ref({ nom: "", description: "", icone: "heart" });
const iconOptions = ["heart", "stethoscope", "tooth", "baby", "flower", "sparkle", "eye", "bone"];

function addSpecialite() {
  if (!newSpec.value.nom.trim()) return;
  specialites.value.push({
    id: newSpec.value.nom.toLowerCase().replace(/\s+/g, "-"),
    nom: newSpec.value.nom,
    description: newSpec.value.description || "Nouvelle spécialité",
    icone: newSpec.value.icone,
    nbMedecins: 0,
  });
  newSpec.value = { nom: "", description: "", icone: "heart" };
  showModal.value = false;
}
function remove(id: string) {
  specialites.value = specialites.value.filter((s) => s.id !== id);
}
</script>

<template>
  <DashboardShell role="admin" :nav="nav" title="Gestion des spécialités">
    <div class="flex items-center justify-between mb-6">
      <p class="text-sm text-ink-500">{{ specialites.length }} spécialités configurées</p>
      <button class="btn-primary !text-sm" @click="showModal = true"><Icon name="plus" class="w-4 h-4" />Ajouter un type</button>
    </div>

    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
      <div v-for="s in specialites" :key="s.id" class="card p-5 flex items-start gap-4">
        <span class="w-11 h-11 rounded-xl bg-azure-50 text-azure-600 flex items-center justify-center shrink-0"><Icon :name="s.icone" class="w-5 h-5" /></span>
        <div class="flex-1 min-w-0">
          <p class="font-display font-semibold text-ink-950">{{ s.nom }}</p>
          <p class="text-xs text-ink-500 mt-0.5">{{ s.description }}</p>
          <p class="text-xs text-ink-400 mt-2">{{ s.nbMedecins }} médecin(s) rattaché(s)</p>
        </div>
        <div class="flex flex-col gap-1.5 shrink-0">
          <button class="btn-ghost !p-2"><Icon name="edit" class="w-4 h-4" /></button>
          <button @click="remove(s.id)" class="btn-ghost !p-2 hover:!text-clay" :disabled="s.nbMedecins > 0" :title="s.nbMedecins > 0 ? 'Des médecins y sont rattachés' : 'Supprimer'">
            <Icon name="x-circle" class="w-4 h-4" />
          </button>
        </div>
      </div>
    </div>

    <div v-if="showModal" class="fixed inset-0 bg-ink-950/50 backdrop-blur-sm z-50 flex items-center justify-center p-4" @click.self="showModal = false">
      <div class="card w-full max-w-md p-6 sm:p-7">
        <div class="flex items-center justify-between mb-5">
          <h2 class="font-display font-semibold text-lg text-ink-950">Nouvelle spécialité</h2>
          <button @click="showModal = false" class="text-ink-400 hover:text-ink-700"><Icon name="x" class="w-5 h-5" /></button>
        </div>
        <div class="space-y-4">
          <div><label class="label">Nom de la spécialité</label><input v-model="newSpec.nom" class="input" placeholder="Ex. Endocrinologie" /></div>
          <div><label class="label">Description courte</label><input v-model="newSpec.description" class="input" placeholder="Ex. Hormones & métabolisme" /></div>
          <div>
            <label class="label">Icône</label>
            <div class="grid grid-cols-8 gap-2">
              <button
                v-for="icon in iconOptions" :key="icon"
                @click="newSpec.icone = icon"
                type="button"
                class="aspect-square rounded-lg flex items-center justify-center border transition-colors"
                :class="newSpec.icone === icon ? 'bg-azure-600 border-azure-600 text-white' : 'border-ink-200 text-ink-500 hover:border-azure-300'"
              >
                <Icon :name="icon" class="w-4 h-4" />
              </button>
            </div>
          </div>
          <button class="btn-primary w-full" @click="addSpecialite">Ajouter la spécialité</button>
        </div>
      </div>
    </div>
  </DashboardShell>
</template>
