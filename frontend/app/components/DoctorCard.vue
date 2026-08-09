<script setup lang="ts">
import type { Medecin } from "~/composables/useMockData";
const props = defineProps<{ medecin: Medecin }>();
const specialites = useSpecialites();
const specialite = computed(() => specialites.find((s) => s.id === props.medecin.specialiteId));
</script>

<template>
  <NuxtLink
    :to="`/medecins/${medecin.id}`"
    class="group flex flex-col bg-white border border-ink-100 rounded-2xl overflow-hidden hover:shadow-card hover:border-azure-200 transition-all duration-300 hover:-translate-y-1"
  >
    <div class="relative h-44 overflow-hidden bg-ink-50">
      <img :src="medecin.photo" :alt="`Dr ${medecin.prenom} ${medecin.nom}`" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" />
      <span class="absolute top-3 left-3 badge bg-white/95 backdrop-blur text-ink-700 shadow-soft">
        <Icon :name="specialite?.icone || 'heart'" class="w-3.5 h-3.5 text-azure-600" />
        {{ specialite?.nom }}
      </span>
    </div>
    <div class="p-5 flex flex-col gap-3 flex-1">
      <div>
        <h3 class="font-display font-semibold text-ink-950">Dr {{ medecin.prenom }} {{ medecin.nom }}</h3>
        <p class="text-sm text-ink-500 mt-1 line-clamp-2">{{ medecin.bio }}</p>
      </div>
      <div class="flex items-center gap-1 text-sm">
        <Icon name="star" class="w-4 h-4 text-amber-400 fill-amber-400" />
        <span class="font-semibold text-ink-800">{{ medecin.note }}</span>
        <span class="text-ink-400">({{ medecin.avis }} avis)</span>
        <span class="text-ink-300 mx-1">·</span>
        <span class="text-ink-500">{{ medecin.experience }} ans d'exp.</span>
      </div>
      <div class="flex items-center justify-between mt-auto pt-3 border-t border-ink-100">
        <div class="text-xs text-ink-500">
          <span class="flex items-center gap-1.5 text-pulse font-medium">
            <span class="w-1.5 h-1.5 rounded-full bg-pulse animate-pulse"></span>
            {{ medecin.prochaineDispo }}
          </span>
        </div>
        <span class="btn-primary !px-4 !py-2 !text-sm">Réserver</span>
      </div>
    </div>
  </NuxtLink>
</template>
