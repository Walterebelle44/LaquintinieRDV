<script setup lang="ts">
definePageMeta({ layout: "auth" });

const route = useRoute();
const router = useRouter();
const { login } = useAuth();

const email = ref("");
const password = ref("");
const loading = ref(false);
const showPassword = ref(false);

function submit() {
  loading.value = true;
  setTimeout(() => {
    login("client");
    loading.value = false;
    const redirect = (route.query.redirect as string) || "/espace-client";
    router.push(redirect);
  }, 700);
}

function quickAccess(role: "client" | "medecin" | "admin") {
  login(role);
  if (role === "client") router.push("/espace-client");
  if (role === "medecin") router.push("/espace-medecin");
  if (role === "admin") router.push("/espace-admin");
}
</script>

<template>
  <div>
    <h1 class="text-2xl font-bold text-ink-950">Content de vous revoir</h1>
    <p class="text-ink-500 mt-2 text-sm">Connectez-vous pour gérer vos rendez-vous.</p>

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

    <div class="my-7 flex items-center gap-3 text-xs text-ink-400">
      <div class="h-px bg-ink-200 flex-1" /> aperçu rapide (démo) <div class="h-px bg-ink-200 flex-1" />
    </div>

    <div class="grid grid-cols-3 gap-2">
      <button @click="quickAccess('client')" class="btn-secondary !px-2 flex-col !py-3 text-xs gap-1.5">
        <Icon name="user" class="w-4 h-4" /> Patient
      </button>
      <button @click="quickAccess('medecin')" class="btn-secondary !px-2 flex-col !py-3 text-xs gap-1.5">
        <Icon name="stethoscope" class="w-4 h-4" /> Médecin
      </button>
      <button @click="quickAccess('admin')" class="btn-secondary !px-2 flex-col !py-3 text-xs gap-1.5">
        <Icon name="shield" class="w-4 h-4" /> Admin
      </button>
    </div>

    <p class="text-center text-sm text-ink-500 mt-8">
      Pas encore de compte ?
      <NuxtLink to="/inscription" class="text-azure-600 font-semibold hover:text-azure-700">Créer un compte</NuxtLink>
    </p>
  </div>
</template>
