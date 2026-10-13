<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { ref, onMounted, onUnmounted, computed } from 'vue';
import axios from 'axios';
import { 
  Stethoscope, 
  Clock, 
  Users, 
  MapPin, 
  CheckCircle2, 
  AlertTriangle, 
  BellRing, 
  RefreshCw, 
  ArrowLeft,
  Share2
} from 'lucide-vue-next';

const props = defineProps({
  token: Object,
  initialStatus: Object,
});

const statusData = ref(props.initialStatus || {});
const isPolling = ref(false);
const lastUpdated = ref(new Date().toLocaleTimeString());
let pollingInterval = null;

const fetchLiveStatus = async () => {
  try {
    isPolling.value = true;
    const res = await axios.get(route('api.patient.status', { token: props.token.id }));
    statusData.value = res.data;
    lastUpdated.value = new Date().toLocaleTimeString();
  } catch (error) {
    console.error('Error fetching token status:', error);
  } finally {
    isPolling.value = false;
  }
};

onMounted(() => {
  pollingInterval = setInterval(fetchLiveStatus, 3000);
});

onUnmounted(() => {
  if (pollingInterval) clearInterval(pollingInterval);
});

const isYourTurn = computed(() => {
  return ['calling', 'serving'].includes(statusData.value.status);
});

const isOnHold = computed(() => {
  return statusData.value.status === 'on_hold';
});

const isCompleted = computed(() => {
  return statusData.value.status === 'completed';
});

const isWaiting = computed(() => {
  return statusData.value.status === 'waiting';
});

const copyLink = () => {
  navigator.clipboard?.writeText(window.location.href);
  alert('Tracking link copied to clipboard!');
};
</script>

<template>
  <Head :title="`Token #${token.token_number} - Live Queue Status`" />

  <div class="min-h-screen bg-slate-50 text-slate-800 selection:bg-teal-500 selection:text-white flex flex-col justify-between">
    <!-- Header -->
    <header class="border-b border-slate-200 bg-white/90 backdrop-blur-md sticky top-0 z-50">
      <div class="max-w-2xl mx-auto px-4 h-16 flex items-center justify-between">
        <Link :href="route('home')" class="flex items-center gap-2 text-slate-500 hover:text-slate-900 text-xs transition font-medium">
          <ArrowLeft class="w-4 h-4" />
          <span>Home</span>
        </Link>
        <div class="flex items-center gap-2">
          <div class="flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-teal-50 border border-teal-200 text-[11px] text-teal-800 font-semibold">
            <span class="w-2 h-2 rounded-full bg-teal-600 animate-pulse"></span>
            <span>Live Sync</span>
          </div>
          <button @click="copyLink" class="p-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 transition" title="Share link">
            <Share2 class="w-4 h-4" />
          </button>
        </div>
      </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-2xl w-full mx-auto px-4 py-8 space-y-6">
      
      <!-- Turn Calling Banner (Vibrant Callout if it's their turn) -->
      <div 
        v-if="isYourTurn" 
        class="p-6 rounded-3xl bg-emerald-600 border border-emerald-500 shadow-xl shadow-emerald-600/20 text-white text-center space-y-2 animate-bounce duration-1000"
      >
        <div class="inline-flex p-3 rounded-2xl bg-white/20 backdrop-blur-md mb-1">
          <BellRing class="w-8 h-8 text-white animate-spin" />
        </div>
        <h2 class="text-2xl font-black tracking-tight">IT'S YOUR TURN NOW!</h2>
        <p class="text-sm font-medium text-emerald-100">
          Please proceed immediately to <span class="font-bold underline text-white">{{ statusData.room_number }}</span>
        </p>
      </div>

      <!-- On Hold Alert Banner (If patient missed their turn) -->
      <div 
        v-else-if="isOnHold" 
        class="p-6 rounded-3xl bg-amber-50 border-2 border-amber-300 text-amber-900 text-center space-y-2 shadow-sm"
      >
        <div class="inline-flex p-3 rounded-2xl bg-amber-100 mb-1">
          <AlertTriangle class="w-7 h-7 text-amber-600" />
        </div>
        <h2 class="text-xl font-bold text-amber-800">Your Turn Was Put On Hold</h2>
        <p class="text-xs text-amber-700 leading-relaxed max-w-md mx-auto">
          The doctor called your number, but you were not present. Don't worry! 
          Please inform the <strong class="text-amber-900">Cashier or Reception desk</strong> right now to get your token recalled into the queue.
        </p>
      </div>

      <!-- Main Token Display Card -->
      <div class="p-8 rounded-3xl bg-white border border-slate-200 shadow-lg relative overflow-hidden text-center space-y-6">
        <div class="space-y-1">
          <span class="text-xs font-bold uppercase tracking-widest text-slate-400">Your Token Number</span>
          <div class="text-7xl sm:text-8xl font-black tracking-tight text-teal-700 font-mono">
            #{{ token.token_number }}
          </div>
          <p class="text-sm text-slate-600 font-medium">Patient: <strong class="text-slate-900">{{ token.patient?.name }}</strong></p>
        </div>

        <!-- Status Pill -->
        <div class="inline-block">
          <span 
            v-if="isWaiting" 
            class="px-4 py-1.5 rounded-full text-xs font-bold bg-sky-50 border border-sky-200 text-sky-700 uppercase tracking-wider"
          >
            In Waiting Queue
          </span>
          <span 
            v-else-if="isYourTurn" 
            class="px-4 py-1.5 rounded-full text-xs font-bold bg-emerald-100 border border-emerald-300 text-emerald-800 uppercase tracking-wider animate-pulse"
          >
            Now Calling / In Room
          </span>
          <span 
            v-else-if="isOnHold" 
            class="px-4 py-1.5 rounded-full text-xs font-bold bg-amber-100 border border-amber-300 text-amber-800 uppercase tracking-wider"
          >
            Missed / On Hold
          </span>
          <span 
            v-else-if="isCompleted" 
            class="px-4 py-1.5 rounded-full text-xs font-bold bg-slate-100 border border-slate-200 text-slate-600 uppercase tracking-wider"
          >
            Completed
          </span>
        </div>

        <!-- Queue Progression Meter -->
        <div v-if="isWaiting" class="grid grid-cols-2 gap-4 pt-4 border-t border-slate-100">
          <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/80 text-left">
            <div class="flex items-center gap-2 text-slate-500 text-xs mb-1">
              <Users class="w-4 h-4 text-teal-600" />
              <span>Patients Ahead</span>
            </div>
            <div class="text-3xl font-black text-slate-900">
              {{ statusData.patientsAhead ?? 0 }}
            </div>
            <p class="text-[10px] text-slate-400 mt-0.5">people before your turn</p>
          </div>

          <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/80 text-left">
            <div class="flex items-center gap-2 text-slate-500 text-xs mb-1">
              <Clock class="w-4 h-4 text-cyan-600" />
              <span>Est. Wait Time</span>
            </div>
            <div class="text-3xl font-black text-slate-900">
              ~{{ statusData.estimatedMinutes ?? 0 }} min
            </div>
            <p class="text-[10px] text-slate-400 mt-0.5">estimated countdown</p>
          </div>
        </div>

        <!-- Current Doctor Serving Info -->
        <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 flex items-center justify-between text-left">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-teal-100 text-teal-800 flex items-center justify-center font-bold">
              <Stethoscope class="w-5 h-5" />
            </div>
            <div>
              <h4 class="text-xs font-bold text-slate-900">{{ statusData.doctor_name || 'Doctor' }}</h4>
              <p class="text-[11px] text-slate-500">{{ statusData.doctor_specialization || 'Consultant' }}</p>
            </div>
          </div>
          <div class="text-right">
            <span class="text-[10px] text-slate-400 uppercase tracking-wider block font-medium">Assigned Room</span>
            <span class="text-xs font-bold text-teal-700 font-mono">{{ statusData.room_number || 'Room 01' }}</span>
          </div>
        </div>

        <!-- Currently Serving in Room -->
        <div class="text-xs text-slate-500 flex items-center justify-center gap-2">
          <span>Currently Serving in Clinic:</span>
          <span class="px-2.5 py-0.5 rounded bg-slate-100 border border-slate-200 text-slate-800 font-mono font-bold">
            {{ statusData.currentServingNumber ? `#${statusData.currentServingNumber}` : 'None' }}
          </span>
        </div>
      </div>

      <!-- Sync Status & Manual Refresh -->
      <div class="flex items-center justify-between text-[11px] text-slate-400 px-2">
        <span>Auto-synced every 3s &bull; Last update: {{ lastUpdated }}</span>
        <button @click="fetchLiveStatus" class="flex items-center gap-1 text-teal-600 hover:text-teal-700 font-medium transition">
          <RefreshCw :class="['w-3.5 h-3.5', isPolling ? 'animate-spin' : '']" />
          <span>Refresh</span>
        </button>
      </div>
    </main>

    <!-- Footer -->
    <footer class="py-6 text-center text-xs text-slate-400 border-t border-slate-200 bg-white">
      CareQueue Patient Live Tracker &bull; Please keep this screen open while waiting
    </footer>
  </div>
</template>
