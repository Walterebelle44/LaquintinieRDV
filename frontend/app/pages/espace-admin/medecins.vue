<script setup lang="ts">
import { useApi } from '~/composables/useApi';
import { mapMedecin, mapSpecialite } from '~/composables/useMappers';

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
const { data: medecinsRes, refresh, pending } = await useAsyncData("admin-medecins", () =>
  api.get("/medecins?per_page=100").catch(() => ({ data: [] }))
);
const { data: specialitesRes } = await useAsyncData("admin-medecins-specialites", () =>
  api.get("/specialites").catch(() => ({ data: [] }))
);

const medecins = computed(() => (medecinsRes.value?.data || []).map((m: any) => ({
  ...mapMedecin(m),
  raw: m,
})));
const specialites = computed(() => (specialitesRes.value?.data || []).map(mapSpecialite));

const showModal = ref(false);
const creating = ref(false);
const erreurModal = ref("");
const actionEnCours = ref<string | null>(null);

const form = ref({
  prenom: "", nom: "", email: "", telephone: "", numero_ordre: "",
  specialite_ids: [] as number[], annees_experience: 0, tarif_consultation: 0, biographie: "",
});

async function toggleBlock(m: any) {
  actionEnCours.value = m.id;
  try {
    await api.patch(`/admin/medecins/${m.id}/bloquer`);
    await refresh();
  } catch (e: any) {
    alert(e?.message || "Action impossible.");
  } finally {
    actionEnCours.value = null;
  }
}

async function createMedecin() {
  erreurModal.value = "";
  creating.value = true;
  try {
    await api.post("/admin/medecins", form.value);
    showModal.value = false;
    form.value = { prenom: "", nom: "", email: "", telephone: "", numero_ordre: "", specialite_ids: [], annees_experience: 0, tarif_consultation: 0, biographie: "" };
    await refresh();
  } catch (e: any) {
    erreurModal.value = e?.message || "Impossible de créer le médecin.";
  } finally {
    creating.value = false;
  }
}
</script>

<template>
  <DashboardShell role="admin" :nav="nav" title="Gestion des médecins">
    <div class="flex items-center justify-between mb-6">
      <p class="text-sm text-ink-500">{{ medecins.length }} médecins enregistrés</p>
      <button class="btn-primary !text-sm" @click="showModal = true"><Icon name="plus" class="w-4 h-4" />Ajouter un médecin</button>
    </div>

    <div v-if="pending" class="card p-16 text-center text-sm text-ink-400">Chargement…</div>

    <div v-else class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
      <div v-for="m in medecins" :key="m.id" class="card p-5">
        <div class="flex items-start gap-3">
          <img :src="m.photo" class="w-14 h-14 rounded-xl object-cover" alt="" />
          <div class="flex-1 min-w-0">
            <p class="font-display font-semibold text-ink-950 truncate">Dr {{ m.prenom }} {{ m.nom }}</p>
            <span class="badge bg-azure-50 text-azure-700 mt-1">{{ m.specialiteNom }}</span>
          </div>
          <span class="badge shrink-0" :class="m.statut === 'actif' ? 'badge-confirmed' : 'badge-refused'">{{ m.statut === 'actif' ? 'Actif' : 'Bloqué' }}</span>
        </div>
        <div class="flex items-center gap-4 mt-4 text-xs text-ink-500">
          <span class="flex items-center gap-1"><Icon name="activity" class="w-3.5 h-3.5" />{{ m.experience }} ans</span>
          <span class="flex items-center gap-1"><Icon name="trending-up" class="w-3.5 h-3.5" />{{ m.tarif.toLocaleString() }} F</span>
        </div>
        <div class="flex gap-2 mt-4 pt-4 border-t border-ink-100">
          <button :disabled="actionEnCours === m.id" @click="toggleBlock(m)" class="btn-secondary !py-1.5 !text-xs flex-1" :class="m.statut === 'actif' ? '!text-amber-600 !border-amber-200' : '!text-emerald-600 !border-emerald-200'">
            <Icon :name="m.statut === 'actif' ? 'lock' : 'check-circle'" class="w-3.5 h-3.5" />{{ m.statut === 'actif' ? 'Bloquer' : 'Débloquer' }}
          </button>
        </div>
      </div>
      <div v-if="!medecins.length" class="col-span-full card p-16 text-center text-sm text-ink-500">Aucun médecin enregistré.</div>
    </div>

    <div v-if="showModal" class="fixed inset-0 bg-ink-950/50 backdrop-blur-sm z-50 flex items-center justify-center p-4" @click.self="showModal = false">
      <div class="card w-full max-w-lg p-6 sm:p-7 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between mb-5">
          <h2 class="font-display font-semibold text-lg text-ink-950">Ajouter un médecin</h2>
          <button @click="showModal = false" class="text-ink-400 hover:text-ink-700"><Icon name="x" class="w-5 h-5" /></button>
        </div>
        <div v-if="erreurModal" class="mb-4 p-3 rounded-xl bg-clay-soft text-clay text-sm">{{ erreurModal }}</div>
        <div class="space-y-4">
          <div class="grid grid-cols-2 gap-3">
            <input v-model="form.prenom" class="input" placeholder="Prénom" />
            <input v-model="form.nom" class="input" placeholder="Nom" />
          </div>
          <input v-model="form.email" class="input" type="email" placeholder="Email professionnel" />
          <input v-model="form.telephone" class="input" placeholder="Téléphone" />
          <div class="grid grid-cols-2 gap-3">
            <select v-model="form.specialite_ids" multiple class="input !h-auto">
              <option v-for="s in specialitesRes?.data || []" :key="s.id" :value="s.id">{{ s.nom }}</option>
            </select>
            <input v-model="form.numero_ordre" class="input" placeholder="N° d'ordre (ex. OM-2026-XXXX)" />
          </div>
          <input v-model.number="form.tarif_consultation" class="input" type="number" placeholder="Tarif de consultation (FCFA)" />
          <textarea v-model="form.biographie" class="input" rows="2" placeholder="Biographie courte"></textarea>
          <button class="btn-primary w-full" :disabled="creating" @click="createMedecin">
            {{ creating ? "Création…" : "Créer le compte médecin" }}
          </button>
        </div>
      </div>
    </div>
  </DashboardShell>
</template>
