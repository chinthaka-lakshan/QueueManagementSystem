<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { 
  Stethoscope, 
  UserCheck, 
  Clock, 
  PhoneCall, 
  PauseCircle, 
  CheckCircle2, 
  RotateCcw, 
  LogOut, 
  ChevronRight, 
  Users, 
  AlertCircle,
  Tv,
  ArrowRight,
  UserX,
  Play
} from 'lucide-vue-next';

const props = defineProps({
  doctor: Object,
  session: Object,
  currentServing: Object,
  waitingTokens: Array,
  onHoldTokens: Array,
  completedTokens: Array,
  stats: Object,
});

const isProcessing = ref(false);

const callNext = () => {
  isProcessing.value = true;
  router.post(route('doctor.call-next'), {}, {
    preserveScroll: true,
    onFinish: () => isProcessing.value = false,
  });
};

const callSpecific = (token) => {
  isProcessing.value = true;
  router.post(route('doctor.call', { token: token.id }), {}, {
    preserveScroll: true,
    onFinish: () => isProcessing.value = false,
  });
};

const holdCurrent = (token) => {
  if (confirm(`Put Token #${token.token_number} (${token.patient?.name}) on hold? Next patient can be called.`)) {
    isProcessing.value = true;
    router.post(route('doctor.hold', { token: token.id }), {
      notes: 'Patient not available when called'
    }, {
      preserveScroll: true,
      onFinish: () => isProcessing.value = false,
    });
  }
};

const recallToken = (token) => {
  isProcessing.value = true;
  router.post(route('doctor.recall', { token: token.id }), {}, {
    preserveScroll: true,
    onFinish: () => isProcessing.value = false,
  });
};

const completeCurrent = (token) => {
  isProcessing.value = true;
  router.post(route('doctor.complete', { token: token.id }), {}, {
    preserveScroll: true,
    onFinish: () => isProcessing.value = false,
  });
};

const cancelToken = (token) => {
  if (confirm(`Cancel Token #${token.token_number}?`)) {
    isProcessing.value = true;
    router.post(route('doctor.cancel', { token: token.id }), {}, {
      preserveScroll: true,
      onFinish: () => isProcessing.value = false,
    });
  }
};
</script>

<template>
  <Head :title="`Doctor Console - ${doctor.name}`" />

  <div class="min-h-screen bg-slate-50 text-slate-800 selection:bg-teal-500 selection:text-white pb-16">
    <!-- Top Nav / Doctor Header -->
    <header class="border-b border-slate-200 bg-white/90 backdrop-blur-md sticky top-0 z-40">
      <div class="max-w-4xl mx-auto px-4 h-16 flex items-center justify-between">
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 rounded-2xl bg-teal-50 border border-teal-200 flex items-center justify-center text-teal-600">
            <Stethoscope class="w-5 h-5" />
          </div>
          <div>
            <h1 class="text-sm font-bold text-slate-900 flex items-center gap-2">
              {{ doctor.name }}
              <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-emerald-50 border border-emerald-200 text-emerald-700">
                {{ doctor.room_number || 'Room 01' }}
              </span>
            </h1>
            <p class="text-[11px] text-slate-500">{{ session?.title || 'Daily Session' }}</p>
          </div>
        </div>

        <div class="flex items-center gap-2">
          <Link
            :href="route('display.index', { session: session?.id })"
            target="_blank"
            class="p-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 transition"
            title="Open TV Waiting Screen"
          >
            <Tv class="w-4 h-4 text-teal-600" />
          </Link>
          <Link
            :href="route('logout')"
            method="post"
            as="button"
            class="p-2 rounded-xl bg-slate-100 hover:bg-rose-50 text-slate-500 hover:text-rose-600 transition"
            title="Logout"
          >
            <LogOut class="w-4 h-4" />
          </Link>
        </div>
      </div>
    </header>

    <main class="max-w-4xl mx-auto px-4 pt-6 space-y-6">
      
      <!-- Stats Ribbon -->
      <div class="grid grid-cols-4 gap-2 sm:gap-3">
        <div class="p-3 sm:p-4 rounded-2xl bg-white border border-slate-200 text-center shadow-xs">
          <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Total</span>
          <span class="text-xl sm:text-2xl font-black text-slate-900 font-mono">{{ stats.total }}</span>
        </div>
        <div class="p-3 sm:p-4 rounded-2xl bg-white border border-teal-200 text-center shadow-xs">
          <span class="text-[10px] font-bold text-teal-700 uppercase tracking-wider block">Waiting</span>
          <span class="text-xl sm:text-2xl font-black text-teal-600 font-mono">{{ stats.waiting }}</span>
        </div>
        <div class="p-3 sm:p-4 rounded-2xl bg-white border border-amber-200 text-center shadow-xs">
          <span class="text-[10px] font-bold text-amber-700 uppercase tracking-wider block">On Hold</span>
          <span class="text-xl sm:text-2xl font-black text-amber-600 font-mono">{{ stats.on_hold }}</span>
        </div>
        <div class="p-3 sm:p-4 rounded-2xl bg-white border border-emerald-200 text-center shadow-xs">
          <span class="text-[10px] font-bold text-emerald-700 uppercase tracking-wider block">Done</span>
          <span class="text-xl sm:text-2xl font-black text-emerald-600 font-mono">{{ stats.completed }}</span>
        </div>
      </div>

      <!-- MAIN ACTION HERO: CURRENTLY SERVING PATIENT -->
      <div 
        v-if="currentServing"
        class="p-6 sm:p-8 rounded-3xl bg-white border-2 border-teal-500 shadow-xl shadow-teal-600/10 space-y-6"
      >
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-2">
            <span class="w-3 h-3 rounded-full bg-emerald-500 animate-ping"></span>
            <span class="text-xs font-bold text-teal-800 uppercase tracking-widest">In Room Consultation</span>
          </div>
          <span class="text-xs font-mono px-3 py-1 rounded-full bg-teal-50 border border-teal-200 text-teal-800 uppercase font-semibold">
            {{ currentServing.type }}
          </span>
        </div>

        <!-- Token & Patient Details -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
          <div class="flex items-center gap-5">
            <div class="w-20 h-20 sm:w-24 sm:h-24 rounded-3xl bg-teal-50 border-2 border-teal-500 flex items-center justify-center text-4xl sm:text-5xl font-black text-teal-700 font-mono shadow-sm">
              #{{ currentServing.token_number }}
            </div>
            <div>
              <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">
                {{ currentServing.patient?.name }}
              </h2>
              <div class="flex flex-wrap items-center gap-2 sm:gap-4 text-xs text-slate-500 mt-1">
                <span>{{ currentServing.patient?.gender || 'Patient' }}</span>
                <span v-if="currentServing.patient?.age">&bull; {{ currentServing.patient?.age }} Years</span>
                <span v-if="currentServing.patient?.phone">&bull; {{ currentServing.patient?.phone }}</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Touch Actions: Complete, Skip/Hold, Recall/Announce -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 pt-2">
          <!-- 1. Mark Done & Complete -->
          <button
            @click="completeCurrent(currentServing)"
            :disabled="isProcessing"
            class="py-4 px-4 rounded-2xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-sm tracking-wide shadow-md shadow-emerald-600/20 transition flex items-center justify-center gap-2"
          >
            <CheckCircle2 class="w-5 h-5" />
            <span>Complete Consultation</span>
          </button>

          <!-- 2. Re-Announce / Call Alert -->
          <button
            @click="callSpecific(currentServing)"
            :disabled="isProcessing"
            class="py-4 px-4 rounded-2xl bg-teal-50 hover:bg-teal-100 text-teal-800 border border-teal-200 font-bold text-sm transition flex items-center justify-center gap-2"
          >
            <PhoneCall class="w-5 h-5 text-teal-600" />
            <span>Call Again (Alert TV)</span>
          </button>

          <!-- 3. Hold / Skip (If patient is not here) -->
          <button
            @click="holdCurrent(currentServing)"
            :disabled="isProcessing"
            class="py-4 px-4 rounded-2xl bg-amber-50 hover:bg-amber-100 text-amber-800 border border-amber-300 font-bold text-sm transition flex items-center justify-center gap-2"
          >
            <PauseCircle class="w-5 h-5 text-amber-600" />
            <span>Patient Not Here (Hold)</span>
          </button>
        </div>
      </div>

      <!-- No Patient Currently Serving -> Big Call Next Button -->
      <div 
        v-else 
        class="p-8 sm:p-10 rounded-3xl bg-white border border-slate-200 shadow-sm text-center space-y-6"
      >
        <div class="space-y-2">
          <div class="w-16 h-16 rounded-3xl bg-teal-50 border border-teal-200 flex items-center justify-center mx-auto text-teal-600">
            <Users class="w-8 h-8" />
          </div>
          <h2 class="text-xl sm:text-2xl font-bold text-slate-900">No Patient In Consultation Room</h2>
          <p class="text-xs text-slate-500 max-w-sm mx-auto">
            Ready for next patient? Click below to call the next waiting number into your room.
          </p>
        </div>

        <button
          @click="callNext"
          :disabled="isProcessing || waitingTokens.length === 0"
          class="w-full sm:w-auto min-w-[280px] py-4 px-8 rounded-2xl bg-teal-600 hover:bg-teal-700 text-white font-black text-base tracking-wide shadow-lg shadow-teal-600/25 transition disabled:opacity-50 inline-flex items-center justify-center gap-3"
        >
          <Play class="w-5 h-5 fill-white" />
          <span>CALL NEXT PATIENT</span>
          <span v-if="waitingTokens.length > 0" class="text-xs bg-white/20 px-2 py-0.5 rounded-full font-mono">
            Next: #{{ waitingTokens[0].token_number }}
          </span>
        </button>

        <p v-if="waitingTokens.length === 0" class="text-xs text-amber-700 font-medium">
          No patients waiting in queue right now.
        </p>
      </div>

      <!-- SECTION: ON HOLD / MISSED PATIENTS (Priority Recall) -->
      <div v-if="onHoldTokens.length > 0" class="space-y-3">
        <div class="flex items-center justify-between">
          <h3 class="text-sm font-bold text-amber-800 flex items-center gap-2">
            <AlertCircle class="w-4 h-4 text-amber-600" />
            <span>Missed / On-Hold Queue ({{ onHoldTokens.length }})</span>
          </h3>
          <span class="text-[11px] text-slate-500">Patients who skipped or arrived late</span>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
          <div
            v-for="token in onHoldTokens"
            :key="token.id"
            class="p-4 rounded-2xl bg-amber-50/70 border border-amber-200 flex items-center justify-between shadow-xs"
          >
            <div class="flex items-center gap-3">
              <span class="w-10 h-10 rounded-xl bg-amber-100 border border-amber-300 flex items-center justify-center font-mono font-bold text-amber-800 text-sm">
                #{{ token.token_number }}
              </span>
              <div>
                <h4 class="text-xs font-bold text-slate-900">{{ token.patient?.name }}</h4>
                <p class="text-[10px] text-amber-700">{{ token.notes || 'On hold' }}</p>
              </div>
            </div>

            <!-- Recall Button -->
            <button
              @click="recallToken(token)"
              :disabled="isProcessing"
              class="px-3 py-2 rounded-xl bg-amber-500 hover:bg-amber-600 text-white font-bold text-xs transition flex items-center gap-1 shadow-xs"
            >
              <RotateCcw class="w-3.5 h-3.5" />
              <span>Recall Now</span>
            </button>
          </div>
        </div>
      </div>

      <!-- SECTION: UPCOMING WAITING QUEUE -->
      <div class="space-y-3">
        <div class="flex items-center justify-between">
          <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
            <Users class="w-4 h-4 text-teal-600" />
            <span>Waiting Patients List ({{ waitingTokens.length }})</span>
          </h3>
          <span class="text-[11px] text-slate-400">Sequential order</span>
        </div>

        <div v-if="waitingTokens.length === 0" class="p-6 rounded-2xl bg-white border border-slate-200 text-center text-xs text-slate-400">
          No patients waiting in queue.
        </div>

        <div v-else class="space-y-2">
          <div
            v-for="(token, index) in waitingTokens"
            :key="token.id"
            class="p-4 rounded-2xl bg-white border border-slate-200 hover:border-teal-300 flex items-center justify-between transition shadow-xs"
          >
            <div class="flex items-center gap-3.5">
              <div class="w-10 h-10 rounded-xl bg-teal-50 border border-teal-200 flex items-center justify-center font-mono font-bold text-teal-700 text-sm">
                #{{ token.token_number }}
              </div>
              <div>
                <div class="flex items-center gap-2">
                  <h4 class="text-xs font-bold text-slate-900">{{ token.patient?.name }}</h4>
                  <span class="text-[10px] px-2 py-0.5 rounded bg-slate-100 text-slate-600 uppercase font-mono">
                    {{ token.type }}
                  </span>
                </div>
                <p class="text-[11px] text-slate-500 mt-0.5">
                  Age: {{ token.patient?.age || '-' }} &bull; {{ token.patient?.phone }}
                </p>
              </div>
            </div>

            <!-- Call Out of Order / Direct Call -->
            <button
              @click="callSpecific(token)"
              :disabled="isProcessing"
              class="px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-teal-600 hover:text-white text-slate-700 text-xs font-semibold transition flex items-center gap-1.5"
            >
              <span>Call This</span>
              <ArrowRight class="w-3 h-3" />
            </button>
          </div>
        </div>
      </div>

      <!-- SECTION: COMPLETED PATIENTS -->
      <div v-if="completedTokens.length > 0" class="space-y-3 pt-4 border-t border-slate-200">
        <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider flex items-center gap-2">
          <CheckCircle2 class="w-4 h-4 text-emerald-600" />
          <span>Finished Today ({{ completedTokens.length }})</span>
        </h3>

        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
          <div
            v-for="token in completedTokens"
            :key="token.id"
            class="p-2.5 rounded-xl bg-white border border-slate-200 flex items-center justify-between"
          >
            <span class="font-mono text-xs font-bold text-emerald-700">#{{ token.token_number }}</span>
            <span class="text-[11px] text-slate-700 truncate max-w-[90px]">{{ token.patient?.name }}</span>
          </div>
        </div>
      </div>

    </main>
  </div>
</template>
