<script setup lang="ts">
const specialites = useSpecialites().slice(0, 6);
</script>

<template>
  <footer class="bg-ink-950 text-ink-300 mt-32">
    <div class="section py-16 grid grid-cols-2 md:grid-cols-4 gap-10">
      <div class="col-span-2">
        <div class="flex items-center gap-2.5 mb-4">
          <span class="flex items-center justify-center w-9 h-9 rounded-lg bg-azure-600">
            <Icon name="heart" class="w-4.5 h-4.5 text-white" />
          </span>
          <span class="font-display font-bold text-white text-lg">MediRDV</span>
        </div>
        <p class="text-sm leading-relaxed text-ink-400 max-w-sm">
          La plateforme officielle de prise de rendez-vous en ligne de l'Hôpital Laquintinie de Douala.
          Consultez nos médecins et réservez votre créneau en quelques clics, 24h/24.
        </p>
        <div class="flex items-center gap-3 mt-6 text-sm text-ink-400">
          <Icon name="map-pin" class="w-4 h-4 text-azure-400 shrink-0" />
          <span>Boulevard de la Liberté, Akwa — Douala, Cameroun</span>
        </div>
        <div class="flex items-center gap-3 mt-2 text-sm text-ink-400">
          <Icon name="phone" class="w-4 h-4 text-azure-400 shrink-0" />
          <span>+237 233 42 XX XX &nbsp;·&nbsp; Urgences 24h/24</span>
        </div>
      </div>

      <div>
        <h4 class="font-display font-semibold text-white text-sm mb-4">Spécialités</h4>
        <ul class="space-y-2.5 text-sm">
          <li v-for="s in specialites" :key="s.id">
            <NuxtLink :to="`/medecins?specialite=${s.id}`" class="hover:text-azure-400 transition-colors">{{ s.nom }}</NuxtLink>
          </li>
        </ul>
      </div>

      <div>
        <h4 class="font-display font-semibold text-white text-sm mb-4">Plateforme</h4>
        <ul class="space-y-2.5 text-sm">
          <li><NuxtLink to="/medecins" class="hover:text-azure-400 transition-colors">Nos médecins</NuxtLink></li>
          <li><NuxtLink to="/connexion" class="hover:text-azure-400 transition-colors">Espace patient</NuxtLink></li>
          <li><NuxtLink to="/espace-medecin" class="hover:text-azure-400 transition-colors">Espace médecin</NuxtLink></li>
          <li><NuxtLink to="/espace-admin" class="hover:text-azure-400 transition-colors">Administration</NuxtLink></li>
        </ul>
      </div>
    </div>

    <div class="border-t border-white/10">
      <div class="section py-6 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-ink-500">
        <p>© 2026 Hôpital Laquintinie de Douala — Tous droits réservés.</p>
        <p>Conçu pour un accès aux soins plus simple.</p>
      </div>
    </div>
  </footer>
</template>
