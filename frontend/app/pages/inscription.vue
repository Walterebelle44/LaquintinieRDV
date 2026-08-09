<script setup lang="ts">
definePageMeta({ layout: "auth" });
const router = useRouter();
const { login } = useAuth();

const form = ref({ prenom: "", nom: "", email: "", telephone: "", password: "" });
const loading = ref(false);

function submit() {
  loading.value = true;
  setTimeout(() => {
    login("client");
    loading.value = false;
    router.push("/espace-client");
  }, 700);
}
</script>

<template>
  <div>
    <h1 class="text-2xl font-bold text-ink-950">Créer votre compte patient</h1>
    <p class="text-ink-500 mt-2 text-sm">Réservez vos rendez-vous en quelques secondes.</p>

    <form @submit.prevent="submit" class="mt-8 space-y-5">
      <div class="grid grid-cols-2 gap-3">
        <div>
          <label class="label">Prénom</label>
          <input v-model="form.prenom" type="text" required class="input" placeholder="Walter" />
        </div>
        <div>
          <label class="label">Nom</label>
          <input v-model="form.nom" type="text" required class="input" placeholder="Djoko" />
        </div>
      </div>
      <div>
        <label class="label">Adresse email</label>
        <input v-model="form.email" type="email" required class="input" placeholder="vous@exemple.com" />
      </div>
      <div>
        <label class="label">Numéro de téléphone</label>
        <input v-model="form.telephone" type="tel" required class="input" placeholder="+237 6XX XXX XXX" />
      </div>
      <div>
        <label class="label">Mot de passe</label>
        <input v-model="form.password" type="password" required class="input" placeholder="8 caractères minimum" />
      </div>
      <label class="flex items-start gap-2.5 text-sm text-ink-600">
        <input type="checkbox" required class="mt-0.5 rounded border-ink-300 text-azure-600 focus:ring-azure-200" />
        J'accepte les conditions d'utilisation et la politique de confidentialité des données médicales.
      </label>
      <button type="submit" class="btn-primary w-full" :disabled="loading">
        <span v-if="loading" class="w-4 h-4 border-2 border-white/40 border-t-white rounded-full animate-spin" />
        <template v-else>Créer mon compte <Icon name="arrow-right" class="w-4 h-4" /></template>
      </button>
    </form>

    <p class="text-center text-sm text-ink-500 mt-8">
      Déjà inscrit ?
      <NuxtLink to="/connexion" class="text-azure-600 font-semibold hover:text-azure-700">Se connecter</NuxtLink>
    </p>
  </div>
</template>
