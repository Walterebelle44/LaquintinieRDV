<template>
  <div class="min-h-screen grid lg:grid-cols-2 bg-white">
    <div class="relative hidden lg:flex flex-col justify-between overflow-hidden bg-gradient-to-br from-ink-950 via-ink-900 to-azure-900 p-12 text-white">
      <div class="absolute inset-0 bg-grid-faint opacity-10" />
      <div class="absolute -bottom-24 -left-24 w-96 h-96 rounded-full bg-azure-600/30 blur-3xl" />

      <NuxtLink to="/" class="relative flex items-center gap-2.5">
        <span class="flex items-center justify-center w-10 h-10 rounded-xl bg-white/10 backdrop-blur">
          <Icon name="heart" class="w-5 h-5 text-white" />
        </span>
        <span class="font-display font-bold text-lg">MediRDV</span>
      </NuxtLink>

      <div class="relative">
        <p class="text-2xl font-display font-semibold leading-snug max-w-md">
          « La prise de rendez-vous en ligne m'a fait gagner un temps précieux. Plus besoin de faire la queue dès l'aube. »
        </p>
        <div class="flex items-center gap-3 mt-6">
          <img src="https://i.pravatar.cc/100?img=47" class="w-11 h-11 rounded-full object-cover" alt="" />
          <div class="text-sm">
            <p class="font-semibold">Marceline E.</p>
            <p class="text-ink-300">Patiente à l'Hôpital Laquintinie</p>
          </div>
        </div>
      </div>

      <div class="relative flex items-center gap-8 text-sm text-ink-300">
        <span class="flex items-center gap-2"><Icon name="shield-check" class="w-4 h-4 text-azure-400" />Données sécurisées</span>
        <span class="flex items-center gap-2"><Icon name="clock" class="w-4 h-4 text-azure-400" />Disponible 24h/24</span>
      </div>
    </div>

    <div class="flex flex-col justify-center px-6 sm:px-16 py-12">
      <div class="w-full max-w-sm mx-auto">
        <NuxtLink to="/" class="lg:hidden flex items-center gap-2.5 mb-10">
          <span class="flex items-center justify-center w-9 h-9 rounded-xl bg-azure-600">
            <Icon name="heart" class="w-4.5 h-4.5 text-white" />
          </span>
          <span class="font-display font-bold text-lg text-ink-950">MediRDV</span>
        </NuxtLink>
        <slot />
      </div>
    </div>
  </div>
</template>
