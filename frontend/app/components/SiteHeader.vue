<script setup lang="ts">
const open = ref(false);
const scrolled = ref(false);

onMounted(() => {
  const onScroll = () => (scrolled.value = window.scrollY > 8);
  window.addEventListener("scroll", onScroll);
  onScroll();
  onUnmounted(() => window.removeEventListener("scroll", onScroll));
});

const nav = [
  { label: "Médecins", to: "/medecins" },
  { label: "Spécialités", to: "/#specialites" },
  { label: "Comment ça marche", to: "/#comment-ca-marche" },
];
</script>

<template>
  <header
    class="sticky top-0 z-50 transition-all duration-300"
    :class="scrolled ? 'bg-white/90 backdrop-blur-lg shadow-soft' : 'bg-white/60 backdrop-blur-sm'"
  >
    <div class="section flex items-center justify-between h-20">
      <NuxtLink to="/" class="flex items-center gap-2.5 group">
        <span class="relative flex items-center justify-center w-10 h-10 rounded-xl bg-azure-600 shadow-soft group-hover:bg-azure-700 transition-colors">
          <Icon name="heart" class="w-5 h-5 text-white" />
        </span>
        <span class="font-display font-bold text-lg text-ink-950 leading-none">
          MediRDV
          <span class="block text-[11px] font-body font-medium text-ink-500 tracking-wide -mt-0.5">Hôpital Laquintinie</span>
        </span>
      </NuxtLink>

      <nav class="hidden lg:flex items-center gap-1">
        <NuxtLink
          v-for="item in nav"
          :key="item.to"
          :to="item.to"
          class="px-4 py-2.5 rounded-lg text-[15px] font-medium text-ink-600 hover:text-azure-700 hover:bg-azure-50 transition-colors"
        >
          {{ item.label }}
        </NuxtLink>
      </nav>

      <div class="hidden lg:flex items-center gap-3">
        <NuxtLink to="/connexion" class="btn-ghost">Se connecter</NuxtLink>
        <NuxtLink to="/inscription" class="btn-primary text-sm">
          Prendre rendez-vous
          <Icon name="arrow-right" class="w-4 h-4" />
        </NuxtLink>
      </div>

      <button class="lg:hidden p-2 text-ink-700" @click="open = !open" aria-label="Ouvrir le menu">
        <Icon :name="open ? 'x' : 'menu'" class="w-6 h-6" />
      </button>
    </div>

    <div v-if="open" class="lg:hidden border-t border-ink-100 bg-white px-6 py-4 space-y-1">
      <NuxtLink v-for="item in nav" :key="item.to" :to="item.to" class="block px-3 py-2.5 rounded-lg text-ink-700 font-medium hover:bg-azure-50" @click="open = false">
        {{ item.label }}
      </NuxtLink>
      <div class="pt-3 flex flex-col gap-2 border-t border-ink-100 mt-3">
        <NuxtLink to="/connexion" class="btn-secondary justify-center" @click="open = false">Se connecter</NuxtLink>
        <NuxtLink to="/inscription" class="btn-primary justify-center" @click="open = false">Prendre rendez-vous</NuxtLink>
      </div>
    </div>
  </header>
</template>
