<script setup lang="ts">
const route = useRoute();
const router = useRouter();
const { isLoggedIn } = useAuth();

const medecins = useMedecins();
const specialites = useSpecialites();

const medecin = computed(() => {
  return medecins.find((m) => m.id === route.params.id) || medecins[0] || null;
});

const specialite = computed(() => {
  const specId = medecin.value?.specialiteId ?? null;
  return specId ? specialites.find((s) => s.id === specId) : null;
});

// --- Booking flow state ---
const step = ref(1); // 1: date, 2: heure, 3: motif, 4: confirmation
const selectedDay = ref<number | null>(null);
const selectedSlot = ref<string | null>(null);
const motif = ref("");
const booked = ref(false);
const ticketNumber = `LQT-2026-${Math.floor(10000 + Math.random() * 89999)}`;

const days = computed(() => {
  const today = new Date(2026, 7, 6); // 6 août 2026 (cohérent avec la date système)
  const list = [];
  for (let i = 0; i < 7; i++) {
    const d = new Date(today);
    d.setDate(today.getDate() + i);
    const jourAbrev = d.toLocaleDateString("fr-FR", { weekday: "short" }).replace(".", "");
    const jourAbrevCap = jourAbrev.charAt(0).toUpperCase() + jourAbrev.slice(1);
    list.push({
      index: i,
      label: jourAbrevCap,
      date: d.getDate(),
      mois: d.toLocaleDateString("fr-FR", { month: "short" }),
      dispo: medecin.value?.joursDispo.includes(jourAbrevCap.slice(0, 3)) ?? false,
      full: d,
    });
  }
  return list;
});

const slots = ["08:00", "08:30", "09:00", "09:30", "10:30", "11:00", "14:00", "14:30", "15:00", "15:30", "16:30", "17:00"];
const bookedSlots = ["09:00", "14:30", "16:30"]; // simulate taken slots

function selectDay(i: number) {
  selectedDay.value = i;
  selectedSlot.value = null;
}
function goToStep(s: number) {
  if (!isLoggedIn.value && s > 0) {
    router.push({ path: "/connexion", query: { redirect: route.fullPath } });
    return;
  }
  step.value = s;
}
function confirmBooking() {
  if (!isLoggedIn.value) {
    router.push({ path: "/connexion", query: { redirect: route.fullPath } });
    return;
  }
  booked.value = true;
  step.value = 4;
}

const selectedDayLabel = computed(() => {
  if (selectedDay.value === null) return "";
  const d = days.value[selectedDay.value];
  if (!d) return "";
  return `${d.label} ${d.date} ${d.mois}`;
});
</script>

<template>
  <div class="bg-ink-50/40 min-h-screen pb-20">
    <!-- Breadcrumb -->
    <div class="section pt-8 pb-4">
      <div class="flex items-center gap-2 text-sm text-ink-500">
        <NuxtLink to="/medecins" class="hover:text-azure-600">Médecins</NuxtLink>
        <Icon name="chevron-right" class="w-3.5 h-3.5" />
        <span v-if="medecin" class="text-ink-800 font-medium">Dr {{ medecin.prenom }} {{ medecin.nom }}</span>
        <span v-else class="text-ink-800 font-medium">Dr —</span>
      </div>
    </div>

    <div class="section grid lg:grid-cols-[1fr_400px] gap-10 items-start" v-if="medecin">
      <!-- Colonne profil -->
      <div class="space-y-6">
        <div class="card p-6 sm:p-8">
          <div class="flex flex-col sm:flex-row gap-6">
            <img :src="medecin.photo" class="w-28 h-28 rounded-2xl object-cover shrink-0" :alt="medecin.nom" />
            <div class="flex-1">
              <div class="flex items-start justify-between gap-4 flex-wrap">
                <div>
                  <span class="badge bg-azure-50 text-azure-700 mb-2">
                    <Icon :name="specialite?.icone || 'heart'" class="w-3.5 h-3.5" />
                    {{ specialite?.nom }}
                  </span>
                  <h1 class="text-2xl font-bold text-ink-950">Dr {{ medecin.prenom }} {{ medecin.nom }}</h1>
                  <p class="text-ink-500 mt-1 flex items-center gap-1.5 text-sm">
                    <Icon name="map-pin" class="w-4 h-4" /> Hôpital Laquintinie — Bâtiment B
                  </p>
                </div>
              </div>
              <div class="flex flex-wrap items-center gap-5 mt-4 text-sm">
                <span class="flex items-center gap-1.5"><Icon name="star" class="w-4 h-4 text-amber-400" /><b class="text-ink-800">{{ medecin.note }}</b><span class="text-ink-400">({{ medecin.avis }} avis)</span></span>
                <span class="flex items-center gap-1.5 text-ink-600"><Icon name="activity" class="w-4 h-4 text-azure-600" />{{ medecin.experience }} ans d'expérience</span>
                <span class="flex items-center gap-1.5 text-ink-600"><Icon name="trending-up" class="w-4 h-4 text-azure-600" />{{ medecin.tarif.toLocaleString() }} FCFA / consultation</span>
              </div>
            </div>
          </div>

          <p class="text-ink-600 leading-relaxed mt-6 pt-6 border-t border-ink-100">{{ medecin.bio }}</p>
        </div>

        <div class="card p-6 sm:p-8">
          <h2 class="font-display font-semibold text-ink-900 mb-4">Jours de consultation</h2>
          <div class="flex flex-wrap gap-2">
            <span
              v-for="j in ['Lun','Mar','Mer','Jeu','Ven','Sam']"
              :key="j"
              class="px-3 py-1.5 rounded-lg text-sm font-medium"
              :class="medecin.joursDispo.includes(j) ? 'bg-pulse-soft text-emerald-700' : 'bg-ink-50 text-ink-300 line-through'"
            >
              {{ j }}
            </span>
          </div>
        </div>

        <div class="card p-6 sm:p-8 bg-ink-950 text-white border-none">
          <div class="flex items-start gap-3">
            <Icon name="shield-check" class="w-5 h-5 text-azure-400 mt-0.5 shrink-0" />
            <p class="text-sm text-ink-300 leading-relaxed">
              Une fois votre rendez-vous confirmé par le médecin, vous recevrez un ticket unique
              avec QR code à présenter à l'accueil. Vous pouvez l'imprimer ou le garder sur votre téléphone.
            </p>
          </div>
        </div>
      </div>

      <!-- Colonne réservation (sticky) -->
      <div class="card p-6 sm:p-7 lg:sticky lg:top-28">
        <template v-if="!booked">
          <div class="flex items-center justify-between mb-6">
            <h2 class="font-display font-bold text-lg text-ink-950">Prendre rendez-vous</h2>
            <span class="font-mono text-xs text-ink-400">Étape {{ Math.min(step,3) }}/3</span>
          </div>

          <!-- Progress -->
          <div class="flex items-center gap-1.5 mb-7">
            <div v-for="i in 3" :key="i" class="h-1.5 flex-1 rounded-full" :class="step >= i ? 'bg-azure-600' : 'bg-ink-100'" />
          </div>

          <!-- Step 1: Date -->
          <div v-if="step === 1">
            <p class="label mb-3">Choisissez une date</p>
            <div class="grid grid-cols-4 gap-2 mb-6">
              <button
                v-for="d in days"
                :key="d.index"
                :disabled="!d.dispo"
                @click="selectDay(d.index)"
                class="flex flex-col items-center justify-center gap-0.5 rounded-xl py-3 border transition-all text-sm"
                :class="[
                  !d.dispo ? 'opacity-30 cursor-not-allowed border-ink-100' :
                  selectedDay === d.index ? 'bg-azure-600 border-azure-600 text-white shadow-soft' : 'border-ink-200 hover:border-azure-300 text-ink-700'
                ]"
              >
                <span class="text-[11px] uppercase font-medium opacity-80">{{ d.label }}</span>
                <span class="font-display font-bold text-base">{{ d.date }}</span>
              </button>
            </div>
            <button class="btn-primary w-full" :disabled="selectedDay === null" @click="goToStep(2)">
              Continuer <Icon name="arrow-right" class="w-4 h-4" />
            </button>
          </div>

          <!-- Step 2: Heure -->
          <div v-else-if="step === 2">
            <button class="flex items-center gap-1 text-sm text-ink-500 hover:text-azure-600 mb-4" @click="step = 1">
              <Icon name="chevron-left" class="w-4 h-4" /> {{ selectedDayLabel }}
            </button>
            <p class="label mb-3">Choisissez un créneau</p>
            <div class="grid grid-cols-3 gap-2 mb-6">
              <button
                v-for="s in slots"
                :key="s"
                :disabled="bookedSlots.includes(s)"
                @click="selectedSlot = s"
                class="rounded-xl py-2.5 text-sm font-medium border transition-all"
                :class="[
                  bookedSlots.includes(s) ? 'opacity-30 cursor-not-allowed border-ink-100 line-through' :
                  selectedSlot === s ? 'bg-azure-600 border-azure-600 text-white shadow-soft' : 'border-ink-200 hover:border-azure-300 text-ink-700'
                ]"
              >
                {{ s }}
              </button>
            </div>
            <button class="btn-primary w-full" :disabled="!selectedSlot" @click="goToStep(3)">
              Continuer <Icon name="arrow-right" class="w-4 h-4" />
            </button>
          </div>

          <!-- Step 3: Motif + récap -->
          <div v-else-if="step === 3">
            <button class="flex items-center gap-1 text-sm text-ink-500 hover:text-azure-600 mb-4" @click="step = 2">
              <Icon name="chevron-left" class="w-4 h-4" /> {{ selectedDayLabel }} à {{ selectedSlot }}
            </button>
            <label class="label">Motif de la consultation</label>
            <textarea v-model="motif" rows="3" class="input mb-5" placeholder="Décrivez brièvement le motif (ex. douleurs, contrôle, suivi…)"></textarea>

            <div class="bg-ink-50 rounded-xl p-4 space-y-2.5 mb-6 text-sm">
              <div class="flex justify-between"><span class="text-ink-500">Médecin</span><span class="font-medium text-ink-800">Dr {{ medecin.prenom }} {{ medecin.nom }}</span></div>
              <div class="flex justify-between"><span class="text-ink-500">Date</span><span class="font-medium text-ink-800">{{ selectedDayLabel }}</span></div>
              <div class="flex justify-between"><span class="text-ink-500">Heure</span><span class="font-medium text-ink-800">{{ selectedSlot }}</span></div>
              <div class="flex justify-between border-t border-ink-200 pt-2.5"><span class="text-ink-500">Tarif consultation</span><span class="font-semibold text-ink-900">{{ medecin.tarif.toLocaleString() }} FCFA</span></div>
            </div>

            <button v-if="!isLoggedIn" class="btn-primary w-full" @click="confirmBooking">
              <Icon name="lock" class="w-4 h-4" /> Se connecter pour confirmer
            </button>
            <button v-else class="btn-primary w-full" @click="confirmBooking">
              Confirmer le rendez-vous <Icon name="check" class="w-4 h-4" />
            </button>
            <p v-if="!isLoggedIn" class="text-xs text-ink-400 text-center mt-3">
              Un compte est nécessaire pour réserver. Votre créneau est conservé pendant 5 minutes.
            </p>
          </div>
        </template>

        <!-- Confirmation -->
        <template v-else>
          <div class="text-center py-2">
            <div class="w-16 h-16 rounded-2xl bg-pulse-soft flex items-center justify-center mx-auto mb-5">
              <Icon name="check" class="w-8 h-8 text-pulse" />
            </div>
            <h2 class="font-display font-bold text-xl text-ink-950">Demande envoyée !</h2>
            <p class="text-sm text-ink-500 mt-2 leading-relaxed">
              Votre demande de rendez-vous a été transmise à Dr {{ medecin.prenom }} {{ medecin.nom }}.
              Vous recevrez une confirmation dès validation.
            </p>

            <div class="mt-6 bg-ink-50 rounded-xl p-5 text-left space-y-2.5 text-sm">
              <div class="flex justify-between"><span class="text-ink-500">N° de ticket</span><span class="font-mono font-semibold text-azure-700">{{ ticketNumber }}</span></div>
              <div class="flex justify-between"><span class="text-ink-500">Date</span><span class="font-medium text-ink-800">{{ selectedDayLabel }}</span></div>
              <div class="flex justify-between"><span class="text-ink-500">Heure</span><span class="font-medium text-ink-800">{{ selectedSlot }}</span></div>
              <div class="flex justify-between"><span class="text-ink-500">Statut</span><span class="badge badge-pending">En attente</span></div>
            </div>

            <div class="flex gap-3 mt-6">
              <button class="btn-secondary flex-1"><Icon name="printer" class="w-4 h-4" />Imprimer</button>
              <NuxtLink to="/espace-client/rendez-vous" class="btn-primary flex-1">Voir mes RDV</NuxtLink>
            </div>
          </div>
        </template>
      </div>
    </div>
  </div>
</template>
