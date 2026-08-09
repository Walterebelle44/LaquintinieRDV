<script setup lang="ts">
withDefaults(defineProps<{ color?: string; animated?: boolean }>(), {
  color: "#2570f5",
  animated: true,
});
</script>

<template>
  <svg
    class="w-full h-auto overflow-visible"
    viewBox="0 0 600 60"
    fill="none"
    preserveAspectRatio="none"
  >
    <path
      d="M0 30 H180 L200 30 L212 8 L228 52 L242 30 L256 30 L268 16 L280 44 L292 30 L310 30 H600"
      :stroke="color"
      stroke-width="2.5"
      stroke-linecap="round"
      stroke-linejoin="round"
      :class="animated ? 'animate-pulseline' : ''"
      :style="animated ? 'stroke-dasharray: 600' : ''"
    />
  </svg>
</template>
