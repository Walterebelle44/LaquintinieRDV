<script setup lang="ts">
import { useApi } from '~/composables/useApi';
import { mapMedecin, mapSpecialite } from '~/composables/useMappers';

const route = useRoute();
const api = useApi();

const { data: specialitesRes } = await useAsyncData("liste-specialites", () =>
  api.get("/specialites").catch(() => ({ data: [] }))
);
const { data: medecinsRes } = await useAsyncData("liste-medecins", () =>
  api.get("/medecins?per_page=100").catch(() => ({ data: [] }))
);

const specialites = computed(() => (specialitesRes.value?.data || []).map(mapSpecialite));
const allMedecins = computed(() => (medecinsRes.value?.data || []).map(mapMedecin));

const activeSpecialite = ref((route.query.specialite as string) || "all");
const search = ref((route.query.q as string) || "");
const sort = ref("dispo");

watch(() => route.query.specialite, (v) => { if (v) activeSpecialite.value = v as string; });

const filtered = computed(() => {
  let list = allMedecins.value.filter((m: { statut: string; }) => m.statut === "actif");
  if (activeSpecialite.value !== "all") list = list.filter((m: { specialiteId: string; }) => m.specialiteId === activeSpecialite.value);
  if (search.value.trim()) {
    const q = search.value.toLowerCase();
    list = list.filter(
      (m: { prenom: any; nom: any; specialiteId: any; }) =>
        `${m.prenom} ${m.nom}`.toLowerCase().includes(q) ||
        specialites.value.find((s: { id: any; }) => s.id === m.specialiteId)?.nom.toLowerCase().includes(q)
    );
  }
  if (sort.value === "note") list = [...list].sort((a, b) => b.note - a.note);
  if (sort.value === "prix") list = [...list].sort((a, b) => a.tarif - b.tarif);
  return list;
});
</script>

<template>
  <div class="bg-ink-50/40 min-h-screen">
    <div class="bg-white border-b border-ink-100">
      <div class="section py-10">
        <span class="eyebrow"><span class="w-1.5 h-1.5 rounded-full bg-azure-600"></span>Annuaire médical</span>
        <h1 class="text-3xl font-bold text-ink-950 mt-2">Trouvez votre médecin</h1>
        <p class="text-ink-500 mt-2">{{ filtered.length }} médecin(s) disponible(s) à l'Hôpital Laquintinie</p>

        <div class="mt-6 flex items-center gap-2 p-1.5 bg-ink-50 border border-ink-200 rounded-xl max-w-lg">
          <Icon name="search" class="w-4.5 h-4.5 text-ink-400 ml-2" />
          <input v-model="search" type="text" placeholder="Nom du médecin ou spécialité…" class="w-full bg-transparent py-2 outline-none text-sm text-ink-800 placeholder:text-ink-400" />
        </div>
      </div>
    </div>

    <div class="section py-10 grid lg:grid-cols-[260px_1fr] gap-10">
      <!-- Sidebar filtres -->
      <aside class="space-y-6">
        <div class="card p-5">
          <h3 class="font-display font-semibold text-ink-900 text-sm mb-4 flex items-center gap-2">
            <Icon name="filter" class="w-4 h-4 text-azure-600" /> Spécialité
          </h3>
          <div class="space-y-1">
            <button
              @click="activeSpecialite = 'all'"
              class="w-full text-left px-3 py-2 rounded-lg text-sm transition-colors flex items-center justify-between"
              :class="activeSpecialite === 'all' ? 'bg-azure-50 text-azure-700 font-semibold' : 'text-ink-600 hover:bg-ink-50'"
            >
              Toutes les spécialités
              <span class="text-xs text-ink-400">{{ allMedecins.length }}</span>
            </button>
            <button
              v-for="s in specialites"
              :key="s.id"
              @click="activeSpecialite = s.id"
              class="w-full text-left px-3 py-2 rounded-lg text-sm transition-colors flex items-center justify-between"
              :class="activeSpecialite === s.id ? 'bg-azure-50 text-azure-700 font-semibold' : 'text-ink-600 hover:bg-ink-50'"
            >
              <span class="flex items-center gap-2"><Icon :name="s.icone" class="w-4 h-4" />{{ s.nom }}</span>
              <span class="text-xs text-ink-400">{{ s.nbMedecins }}</span>
            </button>
          </div>
        </div>

        <div class="card p-5 bg-azure-50/60 border-azure-100">
          <Icon name="shield-check" class="w-6 h-6 text-azure-600 mb-2" />
          <p class="text-sm font-semibold text-ink-900">Réservation sécurisée</p>
          <p class="text-xs text-ink-500 mt-1">Vos données médicales et personnelles sont protégées et confidentielles.</p>
        </div>
      </aside>

      <!-- Résultats -->
      <div>
        <div class="flex items-center justify-between mb-6">
          <p class="text-sm text-ink-500">{{ filtered.length }} résultat(s)</p>
          <select v-model="sort" class="input !w-auto !py-2 text-sm">
            <option value="dispo">Trier par disponibilité</option>
            <option value="note">Trier par note</option>
            <option value="prix">Trier par tarif</option>
          </select>
        </div>

        <div v-if="filtered.length" class="grid sm:grid-cols-2 xl:grid-cols-3 gap-6">
          <DoctorCard v-for="m in filtered" :key="m.id" :medecin="m" />
        </div>

        <div v-else class="card p-16 text-center">
          <Icon name="search" class="w-10 h-10 text-ink-300 mx-auto mb-4" />
          <p class="font-display font-semibold text-ink-800">Aucun médecin trouvé</p>
          <p class="text-sm text-ink-500 mt-1">Essayez une autre spécialité ou un autre mot-clé.</p>
        </div>
      </div>
    </div>
  </div>
</template>
