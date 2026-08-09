<script setup lang="ts">
const nav = [
  { label: "Vue d'ensemble", to: "/espace-admin", icon: "activity" },
  { label: "Utilisateurs", to: "/espace-admin/utilisateurs", icon: "users" },
  { label: "Médecins", to: "/espace-admin/medecins", icon: "stethoscope" },
  { label: "Spécialités", to: "/espace-admin/specialites", icon: "heart" },
  { label: "Rendez-vous", to: "/espace-admin/rendez-vous", icon: "calendar" },
  { label: "Journal des logs", to: "/espace-admin/logs", icon: "file-text" },
];

interface U { id: string; nom: string; email: string; role: "Admin" | "Médecin" | "Patient" | "Secrétaire"; statut: "actif" | "bloque"; inscrit: string; avatar: string; }

const users = ref<U[]>([
  { id: "u1", nom: "Walter Djoko", email: "walter.d@gmail.com", role: "Patient", statut: "actif", inscrit: "12 jan. 2025", avatar: "https://i.pravatar.cc/100?img=8" },
  { id: "u2", nom: "Dr Jean-Paul Ekwalla", email: "j.ekwalla@laquintinie.cm", role: "Médecin", statut: "actif", inscrit: "03 mars 2024", avatar: "https://i.pravatar.cc/100?img=12" },
  { id: "u3", nom: "Marie Essomba", email: "m.essomba@gmail.com", role: "Patient", statut: "actif", inscrit: "22 juin 2025", avatar: "https://i.pravatar.cc/100?img=20" },
  { id: "u4", nom: "Dr Robert Fouda", email: "r.fouda@laquintinie.cm", role: "Médecin", statut: "bloque", inscrit: "14 nov. 2023", avatar: "https://i.pravatar.cc/100?img=14" },
  { id: "u5", nom: "Sylvie Manga", email: "sylvie.m@laquintinie.cm", role: "Secrétaire", statut: "actif", inscrit: "01 fév. 2024", avatar: "https://i.pravatar.cc/100?img=39" },
  { id: "u6", nom: "Admin Système", email: "admin@laquintinie.cm", role: "Admin", statut: "actif", inscrit: "01 jan. 2023", avatar: "https://i.pravatar.cc/100?img=60" },
  { id: "u7", nom: "Alain Fotso", email: "a.fotso@yahoo.fr", role: "Patient", statut: "bloque", inscrit: "18 juil. 2025", avatar: "https://i.pravatar.cc/100?img=17" },
]);

const search = ref("");
const roleFilter = ref("Tous");
const showModal = ref(false);

const filtered = computed(() =>
  users.value.filter(
    (u) =>
      (roleFilter.value === "Tous" || u.role === roleFilter.value) &&
      (u.nom.toLowerCase().includes(search.value.toLowerCase()) || u.email.toLowerCase().includes(search.value.toLowerCase()))
  )
);

function toggleBlock(u: U) {
  u.statut = u.statut === "actif" ? "bloque" : "actif";
}
function remove(u: U) {
  users.value = users.value.filter((x) => x.id !== u.id);
}

const roleColor: Record<string, string> = {
  Admin: "bg-ink-900 text-white",
  Médecin: "bg-azure-50 text-azure-700",
  Patient: "bg-pulse-soft text-emerald-700",
  Secrétaire: "bg-amber-50 text-amber-700",
};
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

    <div class="card overflow-hidden">
      <table class="w-full text-sm">
        <thead class="bg-ink-50/80">
          <tr class="text-left text-xs text-ink-500">
            <th class="px-5 py-3.5 font-medium">Utilisateur</th>
            <th class="px-5 py-3.5 font-medium">Rôle</th>
            <th class="px-5 py-3.5 font-medium">Inscrit le</th>
            <th class="px-5 py-3.5 font-medium">Statut</th>
            <th class="px-5 py-3.5 font-medium text-right">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-ink-100">
          <tr v-for="u in filtered" :key="u.id" class="hover:bg-ink-50/50">
            <td class="px-5 py-3.5">
              <div class="flex items-center gap-3">
                <img :src="u.avatar" class="w-9 h-9 rounded-full object-cover" alt="" />
                <div>
                  <p class="font-medium text-ink-800">{{ u.nom }}</p>
                  <p class="text-xs text-ink-400">{{ u.email }}</p>
                </div>
              </div>
            </td>
            <td class="px-5 py-3.5"><span class="badge" :class="roleColor[u.role]">{{ u.role }}</span></td>
            <td class="px-5 py-3.5 text-ink-500">{{ u.inscrit }}</td>
            <td class="px-5 py-3.5">
              <span class="badge" :class="u.statut === 'actif' ? 'badge-confirmed' : 'badge-refused'">
                {{ u.statut === 'actif' ? 'Actif' : 'Bloqué' }}
              </span>
            </td>
            <td class="px-5 py-3.5">
              <div class="flex items-center justify-end gap-1.5">
                <button class="btn-ghost !p-2" title="Modifier"><Icon name="edit" class="w-4 h-4" /></button>
                <button @click="toggleBlock(u)" class="btn-ghost !p-2" :class="u.statut === 'actif' ? 'hover:!text-amber-600' : 'hover:!text-emerald-600'" :title="u.statut === 'actif' ? 'Bloquer' : 'Débloquer'">
                  <Icon :name="u.statut === 'actif' ? 'lock' : 'check-circle'" class="w-4 h-4" />
                </button>
                <button @click="remove(u)" class="btn-ghost !p-2 hover:!text-clay" title="Supprimer"><Icon name="x-circle" class="w-4 h-4" /></button>
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
        <div class="space-y-4">
          <div><label class="label">Nom complet</label><input class="input" placeholder="Nom Prénom" /></div>
          <div><label class="label">Email</label><input class="input" type="email" placeholder="email@laquintinie.cm" /></div>
          <div><label class="label">Rôle</label>
            <select class="input"><option>Patient</option><option>Médecin</option><option>Secrétaire</option><option>Admin</option></select>
          </div>
          <button class="btn-primary w-full" @click="showModal = false">Créer l'utilisateur</button>
        </div>
      </div>
    </div>
  </DashboardShell>
</template>
