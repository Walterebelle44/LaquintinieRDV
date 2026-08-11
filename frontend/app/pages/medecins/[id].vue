<script setup lang="ts">
import { useApi } from '~/composables/useApi';
import { mapMedecin, mapSpecialite } from '~/composables/useMappers';

const route = useRoute();
const router = useRouter();
const { isLoggedIn, role } = useAuth();
const api = useApi();

const { data: medecinRes } = await useAsyncData(`medecin-${route.params.id}`, () =>
  api.get(`/medecins/${route.params.id}`)
);

const medecin = computed(() => mapMedecin(medecinRes.value?.data));
const specialite = computed(() => {
  const s = medecinRes.value?.data?.specialites?.[0];
  return s ? mapSpecialite(s) : null;
});

// --- Booking flow state ---
const step = ref(1); // 1: date, 2: heure, 3: motif, 4: confirmation
const selectedDay = ref<number | null>(null);
const selectedSlot = ref<string | null>(null);
const motif = ref("");
const booked = ref(false);
const erreur = ref("");
const loadingCreneaux = ref(false);
const loadingBooking = ref(false);
const creneaux = ref<{ heure: string; disponible: boolean }[]>([]);
const resultatRdv = ref<any>(null);

const days = computed(() => {
  const today = new Date();
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
      iso: d.toISOString().slice(0, 10),
      full: d,
    });
  }
  return list;
});

async function selectDay(i: number) {
  selectedDay.value = i;
  selectedSlot.value = null;
  erreur.value = "";
  loadingCreneaux.value = true;
  try {
    const day = days.value[i];
    if (!day) {
      throw new Error("Date invalide.");
    }
    const res = await api.get(`/medecins/${route.params.id}/creneaux`, {
      query: { date: day.iso },
    });
    creneaux.value = res.data || [];
  } catch (e: any) {
    creneaux.value = [];
    erreur.value = e?.message || "Impossible de charger les créneaux.";
  } finally {
    loadingCreneaux.value = false;
  }
}

function goToStep(s: number) {
  if (!isLoggedIn.value) {
    router.push({ path: "/connexion", query: { redirect: route.fullPath } });
    return;
  }
  step.value = s;
}

async function confirmBooking() {
  if (!isLoggedIn.value) {
    router.push({ path: "/connexion", query: { redirect: route.fullPath } });
    return;
  }
  if (role.value !== "patient") {
    erreur.value = "Seul un compte patient peut prendre rendez-vous.";
    return;
  }

  const selectedDayIndex = selectedDay.value;
  const day = typeof selectedDayIndex === "number" ? days.value[selectedDayIndex] : undefined;
  if (!day) {
    erreur.value = "Sélectionnez une date valide.";
    return;
  }

  erreur.value = "";
  loadingBooking.value = true;
  try {
    const res = await api.post("/patient/rendez-vous", {
      medecin_id: Number(route.params.id),
      date: day.iso,
      heure_debut: selectedSlot.value,
      motif: motif.value,
    });
    resultatRdv.value = res.data;
    booked.value = true;
    step.value = 4;
  } catch (e: any) {
    erreur.value = e?.message || "Impossible de confirmer le rendez-vous. Merci de réessayer.";
  } finally {
    loadingBooking.value = false;
  }
}

const selectedDayLabel = computed(() => {
  if (selectedDay.value === null) return "";
  const d = days.value[selectedDay.value];
  return d ? `${d.label} ${d.date} ${d.mois}` : "";
});
</script>

<template>
  <div class="bg-ink-50/40 min-h-screen pb-20" v-if="medecinRes?.data">
    <!-- Breadcrumb -->
    <div class="section pt-8 pb-4">
      <div class="flex items-center gap-2 text-sm text-ink-500">
        <NuxtLink to="/medecins" class="hover:text-azure-600">Médecins</NuxtLink>
        <Icon name="chevron-right" class="w-3.5 h-3.5" />
        <span class="text-ink-800 font-medium">Dr {{ medecin.prenom }} {{ medecin.nom }}</span>
      </div>
    </div>

    <div class="section grid lg:grid-cols-[1fr_400px] gap-10 items-start">
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
                    <Icon name="map-pin" class="w-4 h-4" /> Hôpital Laquintinie {{ medecin.salle ? `— ${medecin.salle}` : '' }}
                  </p>
                </div>
              </div>
              <div class="flex flex-wrap items-center gap-5 mt-4 text-sm">
                <span class="flex items-center gap-1.5 text-ink-600"><Icon name="activity" class="w-4 h-4 text-azure-600" />{{ medecin.experience }} ans d'expérience</span>
                <span class="flex items-center gap-1.5 text-ink-600"><Icon name="trending-up" class="w-4 h-4 text-azure-600" />{{ medecin.tarif.toLocaleString() }} FCFA / consultation</span>
              </div>
            </div>
          </div>

          <p class="text-ink-600 leading-relaxed mt-6 pt-6 border-t border-ink-100">{{ medecin.bio }}</p>
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
        <div v-if="erreur" class="mb-5 p-3.5 rounded-xl bg-clay-soft text-clay text-sm flex items-start gap-2">
          <Icon name="alert-triangle" class="w-4.5 h-4.5 shrink-0 mt-0.5" />
          {{ erreur }}
        </div>

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
                @click="selectDay(d.index); goToStep(2)"
                class="flex flex-col items-center justify-center gap-0.5 rounded-xl py-3 border transition-all text-sm"
                :class="selectedDay === d.index ? 'bg-azure-600 border-azure-600 text-white shadow-soft' : 'border-ink-200 hover:border-azure-300 text-ink-700'"
              >
                <span class="text-[11px] uppercase font-medium opacity-80">{{ d.label }}</span>
                <span class="font-display font-bold text-base">{{ d.date }}</span>
              </button>
            </div>
          </div>

          <!-- Step 2: Heure -->
          <div v-else-if="step === 2">
            <button class="flex items-center gap-1 text-sm text-ink-500 hover:text-azure-600 mb-4" @click="step = 1">
              <Icon name="chevron-left" class="w-4 h-4" /> {{ selectedDayLabel }}
            </button>
            <p class="label mb-3">Choisissez un créneau</p>

            <div v-if="loadingCreneaux" class="py-10 text-center text-sm text-ink-400">
              <span class="inline-block w-5 h-5 border-2 border-ink-200 border-t-azure-600 rounded-full animate-spin mb-2" />
              <p>Chargement des créneaux…</p>
            </div>
            <div v-else-if="!creneaux.length" class="py-10 text-center text-sm text-ink-400">
              Aucun créneau disponible ce jour. Choisissez une autre date.
            </div>
            <div v-else class="grid grid-cols-3 gap-2 mb-6">
              <button
                v-for="c in creneaux"
                :key="c.heure"
                :disabled="!c.disponible"
                @click="selectedSlot = c.heure"
                class="rounded-xl py-2.5 text-sm font-medium border transition-all"
                :class="[
                  !c.disponible ? 'opacity-30 cursor-not-allowed border-ink-100 line-through' :
                  selectedSlot === c.heure ? 'bg-azure-600 border-azure-600 text-white shadow-soft' : 'border-ink-200 hover:border-azure-300 text-ink-700'
                ]"
              >
                {{ c.heure }}
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
            <button v-else class="btn-primary w-full" :disabled="loadingBooking" @click="confirmBooking">
              <span v-if="loadingBooking" class="w-4 h-4 border-2 border-white/40 border-t-white rounded-full animate-spin" />
              <template v-else>Confirmer le rendez-vous <Icon name="check" class="w-4 h-4" /></template>
            </button>
            <p v-if="!isLoggedIn" class="text-xs text-ink-400 text-center mt-3">
              Un compte est nécessaire pour réserver.
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
              <div class="flex justify-between"><span class="text-ink-500">Référence</span><span class="font-mono font-semibold text-azure-700">{{ resultatRdv?.uuid?.slice(0, 8) }}</span></div>
              <div class="flex justify-between"><span class="text-ink-500">Date</span><span class="font-medium text-ink-800">{{ selectedDayLabel }}</span></div>
              <div class="flex justify-between"><span class="text-ink-500">Heure</span><span class="font-medium text-ink-800">{{ selectedSlot }}</span></div>
              <div class="flex justify-between"><span class="text-ink-500">Statut</span><span class="badge badge-pending">En attente de confirmation</span></div>
            </div>

            <div class="flex gap-3 mt-6">
              <NuxtLink to="/espace-client/rendez-vous" class="btn-primary flex-1">Voir mes RDV</NuxtLink>
            </div>
          </div>
        </template>
      </div>
    </div>
  </div>

  <div v-else class="section py-24 text-center text-ink-400">
    Chargement du profil médecin…
  </div>
</template>
