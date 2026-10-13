<script setup>
import { Head, router } from '@inertiajs/vue3';
import { ref, onMounted, onUnmounted, computed, watch } from 'vue';
import axios from 'axios';
import { 
  Tv, 
  Volume2, 
  VolumeX, 
  Maximize2, 
  Clock, 
  Stethoscope, 
  Users, 
  AlertCircle, 
  Sparkles,
  ArrowRight
} from 'lucide-vue-next';

const props = defineProps({
  sessions: Array,
  initialSessionId: [Number, String],
  initialData: Object,
});

const activeSessionId = ref(props.initialSessionId || (props.sessions && props.sessions[0]?.id));
const displayData = ref(props.initialData || {
  session: null,
  currentServing: null,
  lastCalled: [],
  waitingList: [],
  onHoldList: [],
  stats: {},
});

const soundEnabled = ref(false);
const currentTime = ref('');
const currentDate = ref('');
const lastPlayedTokenId = ref(null);
let pollTimer = null;
let clockTimer = null;

const updateClock = () => {
  const now = new Date();
  currentTime.value = now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', second: '2-digit' });
  currentDate.value = now.toLocaleDateString([], { weekday: 'long', year: 'numeric', month: 'short', day: 'numeric' });
};

// Web Audio API Ding-Dong Chime generator
const playChime = () => {
  if (!soundEnabled.value) return;
  try {
    const audioCtx = new (window.AudioContext || window.webkitAudioContext)();
    
    // First high tone
    const osc1 = audioCtx.createOscillator();
    const gain1 = audioCtx.createGain();
    osc1.type = 'sine';
    osc1.frequency.setValueAtTime(880, audioCtx.currentTime); // A5
    gain1.gain.setValueAtTime(0.3, audioCtx.currentTime);
    gain1.gain.exponentialRampToValueAtTime(0.001, audioCtx.currentTime + 0.6);
    osc1.connect(gain1);
    gain1.connect(audioCtx.destination);
    osc1.start(audioCtx.currentTime);
    osc1.stop(audioCtx.currentTime + 0.6);

    // Second lower tone
    const osc2 = audioCtx.createOscillator();
    const gain2 = audioCtx.createGain();
    osc2.type = 'sine';
    osc2.frequency.setValueAtTime(659.25, audioCtx.currentTime + 0.3); // E5
    gain2.gain.setValueAtTime(0.35, audioCtx.currentTime + 0.3);
    gain2.gain.exponentialRampToValueAtTime(0.001, audioCtx.currentTime + 1.0);
    osc2.connect(gain2);
    gain2.connect(audioCtx.destination);
    osc2.start(audioCtx.currentTime + 0.3);
    osc2.stop(audioCtx.currentTime + 1.0);

    // Voice announcement using SpeechSynthesis
    if ('speechSynthesis' in window && displayData.value.currentServing) {
      setTimeout(() => {
        const tokenNum = displayData.value.currentServing.token_number;
        const roomNum = displayData.value.session?.room_number || 'Doctor Room';
        const text = `Token number ${tokenNum}. Please proceed to ${roomNum}.`;
        const utterance = new SpeechSynthesisUtterance(text);
        utterance.rate = 0.9;
        utterance.pitch = 1.0;
        window.speechSynthesis.speak(utterance);
      }, 700);
    }
  } catch (e) {
    console.error('Audio playback error:', e);
  }
};

const fetchLiveData = async () => {
  if (!activeSessionId.value) return;
  try {
    const res = await axios.get(route('api.display.live', { session: activeSessionId.value }));
    const newData = res.data;

    // Detect if newly called token or recalled
    if (newData.currentServing) {
      const newTokenId = `${newData.currentServing.id}_${newData.currentServing.called_at}`;
      if (lastPlayedTokenId.value !== null && lastPlayedTokenId.value !== newTokenId) {
        displayData.value = newData;
        playChime();
        lastPlayedTokenId.value = newTokenId;
        return;
      }
      if (lastPlayedTokenId.value === null) {
        lastPlayedTokenId.value = newTokenId;
      }
    }

    displayData.value = newData;
  } catch (err) {
    console.error('Error fetching live display data:', err);
  }
};

const toggleSound = () => {
  soundEnabled.value = !soundEnabled.value;
  if (soundEnabled.value) {
    playChime();
  }
};

const toggleFullscreen = () => {
  if (!document.fullscreenElement) {
    document.documentElement.requestFullscreen().catch(err => console.error(err));
  } else {
    document.exitFullscreen().catch(err => console.error(err));
  }
};

onMounted(() => {
  updateClock();
  clockTimer = setInterval(updateClock, 1000);
  fetchLiveData();
  pollTimer = setInterval(fetchLiveData, 2500);
});

onUnmounted(() => {
  if (clockTimer) clearInterval(clockTimer);
  if (pollTimer) clearInterval(pollTimer);
});
</script>

<template>
  <Head title="Live Waiting Area Display Screen" />

  <div class="min-h-screen bg-slate-100 text-slate-800 flex flex-col justify-between overflow-hidden selection:bg-teal-500">
    
    <!-- Top Display Header -->
    <header class="bg-white border-b border-slate-200 px-6 sm:px-10 py-4 flex items-center justify-between shadow-xs">
      <div class="flex items-center gap-4">
        <div class="h-12 w-12 rounded-2xl bg-teal-600 flex items-center justify-center shadow-md shadow-teal-600/20 text-white">
          <Stethoscope class="w-7 h-7" />
        </div>
        <div>
          <h1 class="text-2xl font-black tracking-tight text-slate-900 flex items-center gap-2">
            CareQueue <span class="text-xs font-bold text-teal-800 bg-teal-100 border border-teal-200 px-2.5 py-0.5 rounded-full">LIVE</span>
          </h1>
          <p class="text-xs text-slate-500">
            {{ displayData.session?.doctor_name || 'Clinic Consultation' }} &bull; {{ displayData.session?.doctor_specialization || 'OPD' }}
          </p>
        </div>
      </div>

      <!-- Live Clock & Controls -->
      <div class="flex items-center gap-6">
        <div class="text-right">
          <div class="text-2xl sm:text-3xl font-black text-slate-900 font-mono tracking-tight leading-none">
            {{ currentTime }}
          </div>
          <p class="text-xs text-slate-500 mt-1 font-medium">{{ currentDate }}</p>
        </div>

        <div class="flex items-center gap-2">
          <button
            @click="toggleSound"
            :class="[
              'p-3 rounded-2xl border transition flex items-center gap-2 font-bold text-xs shadow-xs',
              soundEnabled
                ? 'bg-teal-600 border-teal-600 text-white'
                : 'bg-white border-slate-200 text-slate-600 hover:text-slate-900'
            ]"
            :title="soundEnabled ? 'Mute Chime Sound' : 'Enable Voice & Chime Sound'"
          >
            <component :is="soundEnabled ? Volume2 : VolumeX" class="w-5 h-5" />
            <span class="hidden sm:inline">{{ soundEnabled ? 'Audio Active' : 'Enable Sound' }}</span>
          </button>

          <button
            @click="toggleFullscreen"
            class="p-3 rounded-2xl bg-white border border-slate-200 hover:bg-slate-50 text-slate-600 transition shadow-xs"
            title="Toggle Fullscreen"
          >
            <Maximize2 class="w-5 h-5" />
          </button>
        </div>
      </div>
    </header>

    <!-- Main Live Screen Area -->
    <main class="flex-1 p-6 sm:p-10 max-w-[1700px] w-full mx-auto grid grid-cols-1 lg:grid-cols-12 gap-8 items-stretch">
      
      <!-- HERO CALLOUT: CURRENTLY SERVING TOKEN (COL 7) -->
      <div class="lg:col-span-7 flex flex-col justify-between p-8 sm:p-12 rounded-3xl bg-white border-2 border-teal-500 shadow-xl relative overflow-hidden">
        
        <div class="flex items-center justify-between z-10">
          <div class="inline-flex items-center gap-2.5 px-4 py-2 rounded-full bg-emerald-100 border border-emerald-300 text-emerald-800 font-bold text-sm tracking-wider uppercase">
            <span class="w-3 h-3 rounded-full bg-emerald-500 animate-ping"></span>
            <span>NOW CALLING</span>
          </div>

          <div class="text-right">
            <span class="text-xs uppercase tracking-widest text-slate-400 block font-bold">Assigned Room</span>
            <span class="text-2xl sm:text-3xl font-black text-teal-700 font-mono">
              {{ displayData.session?.room_number || 'ROOM 01' }}
            </span>
          </div>
        </div>

        <!-- GIANT TOKEN NUMBER -->
        <div class="my-auto py-8 text-center z-10 space-y-3">
          <div v-if="displayData.currentServing" class="space-y-4">
            <div class="text-[9rem] sm:text-[13rem] font-black leading-none tracking-tighter text-teal-700 font-mono drop-shadow-sm animate-pulse">
              #{{ displayData.currentServing.token_number }}
            </div>
            <div class="space-y-1">
              <h2 class="text-3xl sm:text-4xl font-extrabold text-slate-900 tracking-tight">
                {{ displayData.currentServing.patient_name }}
              </h2>
              <p class="text-base text-slate-500 font-medium">
                Please proceed directly to Consultation Room
              </p>
            </div>
          </div>

          <!-- If no token currently serving -->
          <div v-else class="py-16 space-y-3">
            <Users class="w-20 h-20 text-slate-300 mx-auto" />
            <h2 class="text-3xl font-extrabold text-slate-500">Waiting for Next Call...</h2>
            <p class="text-sm text-slate-400">Doctor will call the next patient shortly</p>
          </div>
        </div>

        <!-- Doctor Info Footer on Card -->
        <div class="border-t border-slate-100 pt-6 flex items-center justify-between z-10">
          <div>
            <span class="text-xs text-slate-400 uppercase tracking-wider block font-bold">Doctor in Attendance</span>
            <span class="text-base font-bold text-slate-900">{{ displayData.session?.doctor_name || 'Dr. Samantha Perera' }}</span>
          </div>
          <div class="text-right">
            <span class="text-xs text-slate-400 uppercase tracking-wider block font-bold">Clinic Session</span>
            <span class="text-sm font-semibold text-teal-700">{{ displayData.session?.title || 'General Consultation' }}</span>
          </div>
        </div>
      </div>

      <!-- SIDEBAR: UPCOMING, ON-HOLD & RECENT (COL 5) -->
      <div class="lg:col-span-5 flex flex-col justify-between gap-6">
        
        <!-- 1. MISSED / ON-HOLD PATIENTS ALERT BOX -->
        <div 
          v-if="displayData.onHoldList && displayData.onHoldList.length > 0"
          class="p-6 rounded-3xl bg-amber-50 border-2 border-amber-300 shadow-md space-y-3 animate-pulse"
        >
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-2 text-amber-900 font-bold text-sm">
              <AlertCircle class="w-5 h-5 text-amber-600" />
              <span>MISSED / ON-HOLD TOKENS</span>
            </div>
            <span class="text-xs text-amber-800 font-mono font-bold">{{ displayData.onHoldList.length }} Patients</span>
          </div>

          <div class="flex flex-wrap gap-2 pt-1">
            <div
              v-for="token in displayData.onHoldList"
              :key="token.token_number"
              class="px-3.5 py-1.5 rounded-xl bg-amber-100 border border-amber-300 flex items-center gap-2"
            >
              <span class="font-mono font-black text-amber-800 text-sm">#{{ token.token_number }}</span>
              <span class="text-xs text-slate-800 font-medium truncate max-w-[120px]">{{ token.patient_name }}</span>
            </div>
          </div>

          <p class="text-[11px] text-amber-800 font-medium">
            &bull; If your number is listed above, please report to the <strong>Cashier / Reception Desk</strong> immediately.
          </p>
        </div>

        <!-- 2. UPCOMING NEXT IN QUEUE -->
        <div class="flex-1 p-6 rounded-3xl bg-white border border-slate-200 shadow-sm space-y-4">
          <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider flex items-center gap-2">
              <Users class="w-4 h-4 text-teal-600" />
              <span>Next in Queue</span>
            </h3>
            <span class="text-xs font-mono text-teal-700 font-semibold">Upcoming</span>
          </div>

          <div v-if="displayData.waitingList && displayData.waitingList.length > 0" class="grid grid-cols-4 sm:grid-cols-4 gap-3">
            <div
              v-for="token in displayData.waitingList"
              :key="token.token_number"
              class="p-3.5 rounded-2xl bg-slate-50 border border-slate-200 text-center"
            >
              <span class="text-[10px] uppercase text-slate-400 block font-mono font-bold">Token</span>
              <span class="text-2xl font-black text-slate-900 font-mono">#{{ token.token_number }}</span>
            </div>
          </div>
          <div v-else class="text-center py-6 text-xs text-slate-400">
            No further patients waiting in queue.
          </div>
        </div>

        <!-- 3. RECENTLY COMPLETED / PREVIOUS CALLED -->
        <div class="p-6 rounded-3xl bg-white border border-slate-200 shadow-sm space-y-3">
          <div class="flex items-center justify-between border-b border-slate-100 pb-2">
            <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider">
              Previous Called Numbers
            </h3>
            <span class="text-[11px] font-mono text-slate-400">History</span>
          </div>

          <div class="flex items-center gap-3 overflow-x-auto py-1">
            <div
              v-for="token in displayData.lastCalled"
              :key="token.token_number"
              class="px-4 py-2 rounded-xl bg-slate-50 border border-slate-200 flex items-center gap-2 font-mono shrink-0"
            >
              <span class="text-sm font-bold text-slate-700">#{{ token.token_number }}</span>
              <span class="text-[10px] text-slate-400">{{ token.called_at }}</span>
            </div>
            <div v-if="!displayData.lastCalled?.length" class="text-xs text-slate-400">
              None yet
            </div>
          </div>
        </div>

      </div>

    </main>

    <!-- Bottom Ticker / Clinic Notice -->
    <footer class="bg-white border-t border-slate-200 px-6 py-3 flex items-center justify-between text-xs text-slate-500">
      <div class="flex items-center gap-3">
        <span class="px-2 py-0.5 rounded bg-teal-50 border border-teal-200 text-teal-800 font-bold uppercase text-[10px]">Notice</span>
        <span>Please have your prescription and previous medical reports ready when your number is announced.</span>
      </div>
      <div class="hidden sm:flex items-center gap-2 text-slate-400">
        <span>Powered by CareQueue</span>
      </div>
    </footer>

  </div>
</template>
