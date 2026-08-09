<script setup lang="ts">
const specialites = useSpecialites();
const medecins = useMedecins().slice(0, 6);
const router = useRouter();
const query = ref("");

function onSearch() {
  router.push({ path: "/medecins", query: query.value ? { q: query.value } : {} });
}

const stats = [
  { label: "Médecins actifs", value: "52+", icon: "users" },
  { label: "Spécialités", value: "8", icon: "activity" },
  { label: "Rendez-vous / mois", value: "3 400+", icon: "calendar" },
  { label: "Satisfaction patients", value: "4.8/5", icon: "star" },
];

const steps = [
  { title: "Choisissez un médecin", desc: "Parcourez les spécialités et trouvez le praticien qui correspond à votre besoin.", icon: "search" },
  { title: "Sélectionnez un créneau", desc: "Consultez les disponibilités en temps réel et réservez l'heure qui vous convient.", icon: "calendar" },
  { title: "Recevez votre confirmation", desc: "Un ticket de rendez-vous est généré instantanément, imprimable ou à présenter en ligne.", icon: "check-circle" },
  { title: "Rendez-vous suivi", desc: "Rappels automatiques et suivi de votre statut jusqu'au jour J.", icon: "bell" },
];
</script>

<template>
  <div>
    <!-- HERO -->
    <section class="relative overflow-hidden bg-white">
      <div class="absolute inset-0 bg-grid-faint [mask-image:radial-gradient(ellipse_60%_60%_at_50%_0%,black,transparent)]" />
      <div class="absolute -top-32 -right-32 w-[38rem] h-[38rem] rounded-full bg-azure-100/60 blur-3xl" />
      <div class="absolute top-40 -left-40 w-96 h-96 rounded-full bg-pulse-soft blur-3xl opacity-60" />

      <div class="section relative pt-16 pb-20 lg:pt-24 lg:pb-28">
        <div class="grid lg:grid-cols-2 gap-16 items-center">
          <div class="animate-fadeUp">
            <span class="eyebrow">
              <span class="w-1.5 h-1.5 rounded-full bg-pulse"></span>
              Hôpital Laquintinie · Douala
            </span>
            <h1 class="mt-5 text-4xl sm:text-5xl lg:text-[3.4rem] font-bold leading-[1.08] tracking-tight text-ink-950">
              Votre rendez-vous médical, <span class="text-azure-600">réservé en 2 minutes.</span>
            </h1>
            <p class="mt-6 text-lg text-ink-600 leading-relaxed max-w-lg">
              Consultez la disponibilité de nos médecins en temps réel et prenez rendez-vous en ligne,
              sans passer par la file d'attente. Simple, rapide, sécurisé.
            </p>

            <form @submit.prevent="onSearch" class="mt-8 flex flex-col sm:flex-row gap-3 p-2 bg-white border border-ink-200 rounded-2xl shadow-card max-w-xl">
              <div class="flex items-center gap-2 flex-1 px-3">
                <Icon name="search" class="w-5 h-5 text-ink-400 shrink-0" />
                <input
                  v-model="query"
                  type="text"
                  placeholder="Un médecin, une spécialité (ex. cardiologue)…"
                  class="w-full py-2.5 outline-none text-ink-800 placeholder:text-ink-400 bg-transparent"
                />
              </div>
              <button type="submit" class="btn-primary shrink-0">
                Rechercher
                <Icon name="arrow-right" class="w-4 h-4" />
              </button>
            </form>

            <div class="mt-6 flex flex-wrap items-center gap-2 text-sm text-ink-500">
              <span>Recherches fréquentes :</span>
              <NuxtLink v-for="s in specialites.slice(0,4)" :key="s.id" :to="`/medecins?specialite=${s.id}`" class="px-3 py-1 rounded-full bg-ink-50 hover:bg-azure-50 hover:text-azure-700 transition-colors">
                {{ s.nom }}
              </NuxtLink>
            </div>
          </div>

          <div class="relative animate-fadeUp" style="animation-delay: 0.15s">
            <div class="relative rounded-4xl bg-gradient-to-br from-azure-600 to-ink-900 p-1.5 shadow-lift">
              <div class="rounded-[calc(2rem-6px)] bg-white p-6 sm:p-8">
                <div class="flex items-center justify-between mb-6">
                  <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-ink-400">Ticket de rendez-vous</p>
                    <p class="font-display font-bold text-ink-950 text-lg">LQT-2026-00841</p>
                  </div>
                  <span class="badge badge-confirmed">
                    <Icon name="check" class="w-3.5 h-3.5" />
                    Confirmé
                  </span>
                </div>

                <div class="flex items-center gap-4 pb-5 border-b border-dashed border-ink-200">
                  <img src="https://i.pravatar.cc/300?img=12" class="w-14 h-14 rounded-xl object-cover" alt="Médecin" />
                  <div>
                    <p class="font-display font-semibold text-ink-950">Dr Jean-Paul Ekwalla</p>
                    <p class="text-sm text-ink-500">Cardiologue</p>
                  </div>
                </div>

                <div class="grid grid-cols-2 gap-4 py-5">
                  <div>
                    <p class="text-xs text-ink-400 mb-1">Date</p>
                    <p class="font-semibold text-ink-800 text-sm flex items-center gap-1.5"><Icon name="calendar" class="w-4 h-4 text-azure-600" />07 août 2026</p>
                  </div>
                  <div>
                    <p class="text-xs text-ink-400 mb-1">Heure</p>
                    <p class="font-semibold text-ink-800 text-sm flex items-center gap-1.5"><Icon name="clock" class="w-4 h-4 text-azure-600" />15:30</p>
                  </div>
                </div>

                <PulseLine class="my-2 h-8" />

                <div class="flex items-center justify-between pt-4">
                  <div class="flex items-center gap-1.5 text-xs text-ink-400">
                    <Icon name="map-pin" class="w-3.5 h-3.5" />
                    Bâtiment B — Salle 204
                  </div>
                  <Icon name="qr" class="w-9 h-9 text-ink-800" />
                </div>
              </div>
            </div>

            <div class="absolute -bottom-6 -left-6 hidden sm:flex items-center gap-3 bg-white rounded-2xl shadow-card border border-ink-100 px-4 py-3 animate-floatSlow">
              <span class="w-9 h-9 rounded-full bg-pulse-soft flex items-center justify-center text-pulse">
                <Icon name="check" class="w-4 h-4" />
              </span>
              <div class="text-sm">
                <p class="font-semibold text-ink-800">Rappel envoyé</p>
                <p class="text-ink-400 text-xs">1h avant le rendez-vous</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- STATS -->
    <section class="border-y border-ink-100 bg-ink-50/60">
      <div class="section py-10 grid grid-cols-2 lg:grid-cols-4 gap-8">
        <div v-for="s in stats" :key="s.label" class="flex items-center gap-3">
          <span class="w-11 h-11 rounded-xl bg-white shadow-soft flex items-center justify-center text-azure-600 shrink-0">
            <Icon :name="s.icon" class="w-5 h-5" />
          </span>
          <div>
            <p class="font-display font-bold text-xl text-ink-950">{{ s.value }}</p>
            <p class="text-sm text-ink-500">{{ s.label }}</p>
          </div>
        </div>
      </div>
    </section>

    <!-- SPECIALITES -->
    <section id="specialites" class="section py-24 scroll-mt-24">
      <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 mb-10">
        <div>
          <span class="eyebrow"><span class="w-1.5 h-1.5 rounded-full bg-azure-600"></span>Nos spécialités</span>
          <h2 class="text-3xl font-bold text-ink-950 mt-3">Trouvez le bon spécialiste</h2>
        </div>
        <NuxtLink to="/medecins" class="btn-ghost">
          Voir tous les médecins
          <Icon name="arrow-right" class="w-4 h-4" />
        </NuxtLink>
      </div>
      <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <SpecialiteCard
          v-for="s in specialites"
          :key="s.id"
          :nom="s.nom"
          :icone="s.icone"
          :description="s.description"
          :nb-medecins="s.nbMedecins"
          :to="`/medecins?specialite=${s.id}`"
        />
      </div>
    </section>

    <!-- MEDECINS EN AVANT -->
    <section class="bg-ink-50/60 py-24">
      <div class="section">
        <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 mb-10">
          <div>
            <span class="eyebrow"><span class="w-1.5 h-1.5 rounded-full bg-azure-600"></span>Disponibles aujourd'hui</span>
            <h2 class="text-3xl font-bold text-ink-950 mt-3">Médecins recommandés</h2>
          </div>
          <NuxtLink to="/medecins" class="btn-ghost">
            Explorer tous les profils
            <Icon name="arrow-right" class="w-4 h-4" />
          </NuxtLink>
        </div>
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
          <DoctorCard v-for="m in medecins" :key="m.id" :medecin="m" />
        </div>
      </div>
    </section>

    <!-- COMMENT CA MARCHE -->
    <section id="comment-ca-marche" class="section py-24 scroll-mt-24">
      <div class="text-center max-w-xl mx-auto mb-16">
        <span class="eyebrow justify-center"><span class="w-1.5 h-1.5 rounded-full bg-azure-600"></span>Le parcours patient</span>
        <h2 class="text-3xl font-bold text-ink-950 mt-3">Comment ça marche</h2>
        <p class="text-ink-500 mt-3">Quatre étapes entre vous et votre consultation.</p>
      </div>

      <div class="relative grid sm:grid-cols-2 lg:grid-cols-4 gap-8">
        <div class="hidden lg:block absolute top-7 left-[12%] right-[12%]">
          <PulseLine color="#c7d5e8" :animated="false" class="h-4" />
        </div>
        <div v-for="(step, i) in steps" :key="step.title" class="relative flex flex-col items-center text-center">
          <div class="w-14 h-14 rounded-2xl bg-white border-2 border-azure-100 flex items-center justify-center text-azure-600 shadow-soft relative z-10 mb-5">
            <Icon :name="step.icon" class="w-6 h-6" />
          </div>
          <span class="font-mono text-xs text-azure-500 mb-1">Étape {{ i + 1 }}</span>
          <h3 class="font-display font-semibold text-ink-950">{{ step.title }}</h3>
          <p class="text-sm text-ink-500 mt-2 leading-relaxed">{{ step.desc }}</p>
        </div>
      </div>
    </section>

    <!-- CTA -->
    <section class="section pb-24">
      <div class="relative overflow-hidden rounded-4xl bg-gradient-to-br from-ink-950 via-ink-900 to-azure-900 px-8 py-16 sm:px-16 text-center">
        <div class="absolute inset-0 bg-grid-faint opacity-10 [mask-image:radial-gradient(ellipse_70%_70%_at_50%_50%,black,transparent)]" />
        <div class="relative">
          <h2 class="text-3xl sm:text-4xl font-bold text-white max-w-xl mx-auto leading-tight">
            Ne perdez plus de temps en salle d'attente
          </h2>
          <p class="text-ink-300 mt-4 max-w-md mx-auto">
            Créez votre compte patient gratuitement et réservez votre premier rendez-vous en quelques clics.
          </p>
          <div class="mt-8 flex flex-col sm:flex-row items-center justify-center gap-3">
            <NuxtLink to="/inscription" class="btn-primary !bg-white !text-ink-900 hover:!bg-azure-50">
              Créer mon compte patient
              <Icon name="arrow-right" class="w-4 h-4" />
            </NuxtLink>
            <NuxtLink to="/medecins" class="btn-secondary !bg-transparent !border-white/20 !text-white hover:!border-white/40">
              Voir les médecins
            </NuxtLink>
          </div>
        </div>
      </div>
    </section>
  </div>
</template>
