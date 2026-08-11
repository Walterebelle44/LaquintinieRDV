<script setup lang="ts">
definePageMeta({ layout: "auth" });

const route = useRoute();
const router = useRouter();
const { login, dashboardPath } = useAuth();

const email = ref("");
const password = ref("");
const loading = ref(false);
const showPassword = ref(false);
const erreur = ref("");

async function submit() {
  erreur.value = "";
  loading.value = true;
  try {
    await login(email.value, password.value);
    const redirect = (route.query.redirect as string) || dashboardPath();
    await router.push(redirect);
  } catch (e: any) {
    erreur.value = e?.message || "Impossible de se connecter. Vérifiez vos identifiants.";
  } finally {
    loading.value = false;
  }
}
</script>

<template>
  <div>
    <h1 class="text-2xl font-bold text-ink-950">Content de vous revoir</h1>
    <p class="text-ink-500 mt-2 text-sm">Connectez-vous pour gérer vos rendez-vous.</p>

    <div v-if="erreur" class="mt-6 p-3.5 rounded-xl bg-clay-soft text-clay text-sm flex items-start gap-2">
      <Icon name="alert-triangle" class="w-4.5 h-4.5 shrink-0 mt-0.5" />
      {{ erreur }}
    </div>

    <form @submit.prevent="submit" class="mt-8 space-y-5">
      <div>
        <label class="label">Adresse email</label>
        <input v-model="email" type="email" required class="input" placeholder="vous@exemple.com" />
      </div>
      <div>
        <label class="label">Mot de passe</label>
        <div class="relative">
          <input v-model="password" :type="showPassword ? 'text' : 'password'" required class="input pr-11" placeholder="••••••••" />
          <button type="button" @click="showPassword = !showPassword" class="absolute right-3 top-1/2 -translate-y-1/2 text-ink-400 hover:text-ink-600">
            <Icon :name="showPassword ? 'x' : 'eye'" class="w-4.5 h-4.5" />
          </button>
        </div>
      </div>
      <div class="flex items-center justify-between text-sm">
        <label class="flex items-center gap-2 text-ink-600">
          <input type="checkbox" class="rounded border-ink-300 text-azure-600 focus:ring-azure-200" />
          Se souvenir de moi
        </label>
        <a href="#" class="text-azure-600 font-medium hover:text-azure-700">Mot de passe oublié ?</a>
      </div>
      <button type="submit" class="btn-primary w-full" :disabled="loading">
        <span v-if="loading" class="w-4 h-4 border-2 border-white/40 border-t-white rounded-full animate-spin" />
        <template v-else>Se connecter <Icon name="arrow-right" class="w-4 h-4" /></template>
      </button>
    </form>

    <p class="text-center text-sm text-ink-500 mt-8">
      Pas encore de compte ?
      <NuxtLink to="/inscription" class="text-azure-600 font-semibold hover:text-azure-700">Créer un compte</NuxtLink>
    </p>

    <p class="text-center text-xs text-ink-400 mt-6">
      Comptes de démonstration (seed) : admin@laquintinie.cm · j.ekwalla@laquintinie.cm ·
      walter.d@gmail.com — mot de passe <span class="font-mono">password</span>
    </p>
  </div>
</template>
