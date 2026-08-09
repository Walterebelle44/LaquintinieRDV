<script setup lang="ts">
defineProps<{ nom: string; icone: string; description: string; nbMedecins: number; to: string }>();
</script>

<template>
  <NuxtLink
    :to="to"
    class="group relative flex flex-col gap-4 p-6 bg-white border border-ink-100 rounded-2xl hover:border-azure-200 hover:shadow-card transition-all duration-300 hover:-translate-y-1"
  >
    <div class="w-12 h-12 rounded-xl bg-azure-50 flex items-center justify-center text-azure-600 group-hover:bg-azure-600 group-hover:text-white transition-colors duration-300">
      <Icon :name="icone" class="w-6 h-6" />
    </div>
    <div>
      <h3 class="font-display font-semibold text-ink-950">{{ nom }}</h3>
      <p class="text-sm text-ink-500 mt-1">{{ description }}</p>
    </div>
    <div class="flex items-center justify-between mt-auto pt-2">
      <span class="text-xs font-medium text-ink-400">{{ nbMedecins }} médecins</span>
      <Icon name="arrow-right" class="w-4 h-4 text-ink-300 group-hover:text-azure-600 group-hover:translate-x-1 transition-all" />
    </div>
  </NuxtLink>
</template>
