<script setup lang="ts">
const props = defineProps<{
  role: "client" | "patient" | "medecin" | "admin";
  nav: { label: string; to: string; icon: string }[];
  title: string;
}>();

const { name, logout } = useAuth();
const router = useRouter();
const mobileOpen = ref(false);

const roleLabel = { client: "Espace Patient", patient: "Espace Patient", medecin: "Espace Médecin", admin: "Administration" }[props.role];
const roleBadge = { client: "user", patient: "user", medecin: "stethoscope", admin: "shield" }[props.role];

async function doLogout() {
  await logout();
  router.push("/");
}
</script>

<template>
  <div class="min-h-screen bg-ink-50/50 flex">
    <!-- Sidebar -->
    <aside
      class="fixed lg:sticky top-0 h-screen w-72 bg-ink-950 text-white flex flex-col z-40 transition-transform duration-300"
      :class="mobileOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
    >
      <div class="p-6 flex items-center gap-2.5 border-b border-white/10">
        <span class="flex items-center justify-center w-9 h-9 rounded-xl bg-azure-600 shrink-0">
          <Icon name="heart" class="w-4.5 h-4.5 text-white" />
        </span>
        <div class="leading-tight">
          <p class="font-display font-bold text-sm">MediRDV</p>
          <p class="text-[11px] text-ink-400">{{ roleLabel }}</p>
        </div>
      </div>

      <nav class="flex-1 overflow-y-auto p-4 space-y-1">
        <NuxtLink
          v-for="item in nav"
          :key="item.to"
          :to="item.to"
          class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium text-ink-300 hover:bg-white/5 hover:text-white transition-colors"
          active-class="!bg-azure-600 !text-white shadow-soft"
        >
          <Icon :name="item.icon" class="w-4.5 h-4.5" />
          {{ item.label }}
        </NuxtLink>
      </nav>

      <div class="p-4 border-t border-white/10">
        <NuxtLink to="/" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm text-ink-300 hover:bg-white/5 hover:text-white transition-colors">
          <Icon name="arrow-right" class="w-4.5 h-4.5 rotate-180" />
          Retour au site
        </NuxtLink>
        <button @click="doLogout" class="w-full flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm text-ink-300 hover:bg-clay/10 hover:text-clay transition-colors">
          <Icon name="log-out" class="w-4.5 h-4.5" />
          Déconnexion
        </button>
      </div>
    </aside>

    <div v-if="mobileOpen" class="fixed inset-0 bg-ink-950/50 z-30 lg:hidden" @click="mobileOpen = false" />

    <!-- Main -->
    <div class="flex-1 min-w-0">
      <header class="sticky top-0 z-20 bg-white/90 backdrop-blur border-b border-ink-100">
        <div class="flex items-center justify-between px-6 lg:px-10 h-20">
          <div class="flex items-center gap-3">
            <button class="lg:hidden text-ink-600" @click="mobileOpen = true">
              <Icon name="menu" class="w-6 h-6" />
            </button>
            <h1 class="text-xl font-display font-bold text-ink-950">{{ title }}</h1>
          </div>
          <div class="flex items-center gap-4">
            <button class="relative text-ink-500 hover:text-azure-600">
              <Icon name="bell" class="w-5 h-5" />
              <span class="absolute -top-1 -right-1 w-2 h-2 rounded-full bg-clay" />
            </button>
            <div class="flex items-center gap-2.5 pl-4 border-l border-ink-100">
              <div class="w-9 h-9 rounded-full bg-azure-100 text-azure-700 flex items-center justify-center font-display font-semibold text-sm">
                {{ name.split(" ").map(n => n[0]).join("").slice(0,2) }}
              </div>
              <div class="hidden sm:block text-sm leading-tight">
                <p class="font-semibold text-ink-800">{{ name }}</p>
                <p class="text-xs text-ink-400 flex items-center gap-1"><Icon :name="roleBadge" class="w-3 h-3" />{{ roleLabel }}</p>
              </div>
            </div>
          </div>
        </div>
      </header>

      <main class="p-6 lg:p-10">
        <slot />
      </main>
    </div>
  </div>
</template>
