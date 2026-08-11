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
const { data: usersRes, refresh, pending } = await useAsyncData("admin-users", () =>
  api.get("/admin/utilisateurs?per_page=50").catch(() => ({ data: [] }))
);

const users = computed(() => usersRes.value?.data || []);
const search = ref("");
const roleFilter = ref("Tous");
const showModal = ref(false);
const creating = ref(false);
const newUser = ref({ prenom: "", nom: "", email: "", telephone: "", role: "patient" });
const erreurModal = ref("");
const actionEnCours = ref<number | null>(null);

const roleLabel: Record<string, string> = { admin: "Admin", medecin: "Médecin", patient: "Patient", secretaire: "Secrétaire" };
const roleColor: Record<string, string> = {
  admin: "bg-ink-900 text-white",
  medecin: "bg-azure-50 text-azure-700",
  patient: "bg-pulse-soft text-emerald-700",
  secretaire: "bg-amber-50 text-amber-700",
};

const filtered = computed(() =>
  users.value.filter((u: any) => {
    const roleUtilisateur = u.roles?.[0]?.name;
    const matchRole = roleFilter.value === "Tous" || roleLabel[roleUtilisateur] === roleFilter.value;
    const q = search.value.toLowerCase();
    const matchSearch = !q || u.nom.toLowerCase().includes(q) || u.prenom.toLowerCase().includes(q) || u.email.toLowerCase().includes(q);
    return matchRole && matchSearch;
  })
);

async function toggleBlock(u: any) {
  actionEnCours.value = u.id;
  try {
    await api.patch(`/admin/utilisateurs/${u.id}/bloquer`);
    await refresh();
  } catch (e: any) {
    alert(e?.message || "Action impossible.");
  } finally {
    actionEnCours.value = null;
  }
}

async function remove(u: any) {
  if (!confirm(`Supprimer le compte de ${u.prenom} ${u.nom} ?`)) return;
  actionEnCours.value = u.id;
  try {
    await api.del(`/admin/utilisateurs/${u.id}`);
    await refresh();
  } catch (e: any) {
    alert(e?.message || "Suppression impossible.");
  } finally {
    actionEnCours.value = null;
  }
}

async function createUser() {
  erreurModal.value = "";
  creating.value = true;
  try {
    await api.post("/admin/utilisateurs", newUser.value);
    showModal.value = false;
    newUser.value = { prenom: "", nom: "", email: "", telephone: "", role: "patient" };
    await refresh();
  } catch (e: any) {
    erreurModal.value = e?.message || "Impossible de créer l'utilisateur.";
  } finally {
    creating.value = false;
  }
}
</script>

<template>
  <DashboardShell role="admin" :nav="nav" title="Gestion des utilisateurs">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
      <div class="flex items-center gap-2 p-1.5 bg-white border border-ink-200 rounded-xl max-w-sm flex-1">
        <Icon name="search" class="w-4.5 h-4.5 text-ink-400 ml-2" />
        <input v-model="search" placeholder="Rechercher un utilisateur…" class="w-full bg-transparent py-1.5 outline-none text-sm" />
      </div>
      <div class="flex items-center gap-3">
        <select v-model="roleFilter" class="input !w-auto !py-2.5 text-sm">
          <option>Tous</option><option>Admin</option><option>Médecin</option><option>Patient</option><option>Secrétaire</option>
        </select>
        <button class="btn-primary !text-sm" @click="showModal = true"><Icon name="plus" class="w-4 h-4" />Ajouter</button>
      </div>
    </div>

    <div v-if="pending" class="card p-16 text-center text-sm text-ink-400">Chargement…</div>

    <div v-else class="card overflow-hidden">
      <table class="w-full text-sm">
        <thead class="bg-ink-50/80">
          <tr class="text-left text-xs text-ink-500">
            <th class="px-5 py-3.5 font-medium">Utilisateur</th>
            <th class="px-5 py-3.5 font-medium">Rôle</th>
            <th class="px-5 py-3.5 font-medium">Statut</th>
            <th class="px-5 py-3.5 font-medium text-right">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-ink-100">
          <tr v-for="u in filtered" :key="u.id" class="hover:bg-ink-50/50">
            <td class="px-5 py-3.5">
              <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-full bg-azure-100 text-azure-700 flex items-center justify-center font-display font-semibold text-xs">
                  {{ u.prenom[0] }}{{ u.nom[0] }}
                </div>
                <div>
                  <p class="font-medium text-ink-800">{{ u.prenom }} {{ u.nom }}</p>
                  <p class="text-xs text-ink-400">{{ u.email }}</p>
                </div>
              </div>
            </td>
            <td class="px-5 py-3.5"><span class="badge" :class="roleColor[u.roles?.[0]?.name]">{{ roleLabel[u.roles?.[0]?.name] || u.roles?.[0]?.name }}</span></td>
            <td class="px-5 py-3.5">
              <span class="badge" :class="u.statut === 'actif' ? 'badge-confirmed' : 'badge-refused'">
                {{ u.statut === 'actif' ? 'Actif' : 'Bloqué' }}
              </span>
            </td>
            <td class="px-5 py-3.5">
              <div class="flex items-center justify-end gap-1.5">
                <button :disabled="actionEnCours === u.id" @click="toggleBlock(u)" class="btn-ghost !p-2" :class="u.statut === 'actif' ? 'hover:!text-amber-600' : 'hover:!text-emerald-600'" :title="u.statut === 'actif' ? 'Bloquer' : 'Débloquer'">
                  <Icon :name="u.statut === 'actif' ? 'lock' : 'check-circle'" class="w-4 h-4" />
                </button>
                <button :disabled="actionEnCours === u.id" @click="remove(u)" class="btn-ghost !p-2 hover:!text-clay" title="Supprimer"><Icon name="x-circle" class="w-4 h-4" /></button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
      <div v-if="!filtered.length" class="p-12 text-center text-sm text-ink-500">Aucun utilisateur ne correspond à cette recherche.</div>
    </div>

    <!-- Modal ajout -->
    <div v-if="showModal" class="fixed inset-0 bg-ink-950/50 backdrop-blur-sm z-50 flex items-center justify-center p-4" @click.self="showModal = false">
      <div class="card w-full max-w-md p-6 sm:p-7">
        <div class="flex items-center justify-between mb-5">
          <h2 class="font-display font-semibold text-lg text-ink-950">Ajouter un utilisateur</h2>
          <button @click="showModal = false" class="text-ink-400 hover:text-ink-700"><Icon name="x" class="w-5 h-5" /></button>
        </div>
        <div v-if="erreurModal" class="mb-4 p-3 rounded-xl bg-clay-soft text-clay text-sm">{{ erreurModal }}</div>
        <div class="space-y-4">
          <div class="grid grid-cols-2 gap-3">
            <input v-model="newUser.prenom" class="input" placeholder="Prénom" />
            <input v-model="newUser.nom" class="input" placeholder="Nom" />
          </div>
          <input v-model="newUser.email" class="input" type="email" placeholder="email@laquintinie.cm" />
          <input v-model="newUser.telephone" class="input" placeholder="+237 6XX XXX XXX" />
          <select v-model="newUser.role" class="input">
            <option value="patient">Patient</option>
            <option value="secretaire">Secrétaire</option>
            <option value="admin">Admin</option>
          </select>
          <button class="btn-primary w-full" :disabled="creating" @click="createUser">
            {{ creating ? "Création…" : "Créer l'utilisateur" }}
          </button>
        </div>
      </div>
    </div>
  </DashboardShell>
</template>
