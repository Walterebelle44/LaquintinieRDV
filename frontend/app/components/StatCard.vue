<script setup lang="ts">
defineProps<{ label: string; value: string; icon: string; trend?: string; trendUp?: boolean; color?: string }>();
</script>

<template>
  <div class="card p-5 flex items-start justify-between">
    <div>
      <p class="text-sm text-ink-500">{{ label }}</p>
      <p class="text-2xl font-display font-bold text-ink-950 mt-1.5">{{ value }}</p>
      <p v-if="trend" class="text-xs mt-2 flex items-center gap-1" :class="trendUp ? 'text-emerald-600' : 'text-clay'">
        <Icon name="trending-up" class="w-3.5 h-3.5" :class="!trendUp ? 'rotate-90' : undefined" />
        {{ trend }}
      </p>
    </div>
    <span class="w-11 h-11 rounded-xl flex items-center justify-center shrink-0" :class="color || 'bg-azure-50 text-azure-600'">
      <Icon :name="icon" class="w-5 h-5" />
    </span>
  </div>
</template>
