<script setup lang="ts">
import { useApi } from '~/composables/useApi';

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
const { data: specialitesRes, refresh, pending } = await useAsyncData("admin-specialites", () =>
  api.get("/specialites").catch(() => ({ data: [] }))
);

const specialites = computed(() => specialitesRes.value?.data || []);

const showModal = ref(false);
const creating = ref(false);
const erreurModal = ref("");
const actionEnCours = ref<number | null>(null);
const newSpec = ref({ nom: "", description: "", icone: "heart" });
const iconOptions = ["heart", "stethoscope", "tooth", "baby", "flower", "sparkle", "eye", "bone"];

async function addSpecialite() {
  erreurModal.value = "";
  creating.value = true;
  try {
    await api.post("/admin/specialites", newSpec.value);
    newSpec.value = { nom: "", description: "", icone: "heart" };
    showModal.value = false;
    await refresh();
  } catch (e: any) {
    erreurModal.value = e?.message || "Impossible d'ajouter cette spécialité.";
  } finally {
    creating.value = false;
  }
}

async function remove(s: any) {
  if (!confirm(`Supprimer la spécialité « ${s.nom} » ?`)) return;
  actionEnCours.value = s.id;
  try {
    await api.del(`/admin/specialites/${s.id}`);
    await refresh();
  } catch (e: any) {
    alert(e?.message || "Suppression impossible : des médecins sont encore rattachés.");
  } finally {
    actionEnCours.value = null;
  }
}
</script>

<template>
  <DashboardShell role="admin" :nav="nav" title="Gestion des spécialités">
    <div class="flex items-center justify-between mb-6">
      <p class="text-sm text-ink-500">{{ specialites.length }} spécialités configurées</p>
      <button class="btn-primary !text-sm" @click="showModal = true"><Icon name="plus" class="w-4 h-4" />Ajouter un type</button>
    </div>

    <div v-if="pending" class="card p-16 text-center text-sm text-ink-400">Chargement…</div>

    <div v-else class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
      <div v-for="s in specialites" :key="s.id" class="card p-5 flex items-start gap-4">
        <span class="w-11 h-11 rounded-xl bg-azure-50 text-azure-600 flex items-center justify-center shrink-0"><Icon :name="s.icone || 'heart'" class="w-5 h-5" /></span>
        <div class="flex-1 min-w-0">
          <p class="font-display font-semibold text-ink-950">{{ s.nom }}</p>
          <p class="text-xs text-ink-500 mt-0.5">{{ s.description }}</p>
          <p class="text-xs text-ink-400 mt-2">{{ s.medecins_count ?? 0 }} médecin(s) rattaché(s)</p>
        </div>
        <button :disabled="actionEnCours === s.id" @click="remove(s)" class="btn-ghost !p-2 hover:!text-clay shrink-0" title="Supprimer">
          <Icon name="x-circle" class="w-4 h-4" />
        </button>
      </div>
      <div v-if="!specialites.length" class="col-span-full card p-16 text-center text-sm text-ink-500">Aucune spécialité configurée.</div>
    </div>

    <div v-if="showModal" class="fixed inset-0 bg-ink-950/50 backdrop-blur-sm z-50 flex items-center justify-center p-4" @click.self="showModal = false">
      <div class="card w-full max-w-md p-6 sm:p-7">
        <div class="flex items-center justify-between mb-5">
          <h2 class="font-display font-semibold text-lg text-ink-950">Nouvelle spécialité</h2>
          <button @click="showModal = false" class="text-ink-400 hover:text-ink-700"><Icon name="x" class="w-5 h-5" /></button>
        </div>
        <div v-if="erreurModal" class="mb-4 p-3 rounded-xl bg-clay-soft text-clay text-sm">{{ erreurModal }}</div>
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
          <button class="btn-primary w-full" :disabled="creating" @click="addSpecialite">
            {{ creating ? "Ajout…" : "Ajouter la spécialité" }}
          </button>
        </div>
      </div>
    </div>
  </DashboardShell>
</template>
