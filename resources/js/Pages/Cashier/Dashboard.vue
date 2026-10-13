<script setup>
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import axios from 'axios';
import { 
  Receipt, 
  UserPlus, 
  Printer, 
  Search, 
  Clock, 
  RotateCcw, 
  LogOut, 
  Tv, 
  CheckCircle2, 
  AlertCircle, 
  Filter, 
  User, 
  X,
  Stethoscope
} from 'lucide-vue-next';

const props = defineProps({
  cashier: Object,
  sessions: Array,
  currentSession: Object,
  tokens: Array,
  doctors: Array,
  recentlyIssuedToken: Object,
});

const form = useForm({
  session_id: props.currentSession?.id || '',
  name: '',
  phone: '',
  nic: '',
  age: '',
  gender: 'Male',
});

const isSearchingPhone = ref(false);
const showPrintModal = ref(!!props.recentlyIssuedToken);
const printTokenData = ref(props.recentlyIssuedToken || null);
const activeFilter = ref('all');

const onPhoneInput = async () => {
  if (form.phone.length >= 9) {
    try {
      isSearchingPhone.value = true;
      const res = await axios.get(route('cashier.search-patient', { q: form.phone }));
      if (res.data.length > 0) {
        const found = res.data[0];
        form.name = found.name;
        form.age = found.age || '';
        form.gender = found.gender || 'Male';
        form.nic = found.nic || '';
      }
    } catch (e) {
      console.error(e);
    } finally {
      isSearchingPhone.value = false;
    }
  }
};

const issueToken = () => {
  form.post(route('cashier.issue-token'), {
    preserveScroll: true,
    onSuccess: (page) => {
      if (page.props.flash?.issuedToken) {
        printTokenData.value = page.props.flash.issuedToken;
        showPrintModal.value = true;
      }
      form.reset('name', 'phone', 'nic', 'age');
    },
  });
};

const switchSession = (sessionId) => {
  router.get(route('cashier.dashboard'), { session_id: sessionId }, { preserveState: true });
};

const recallPatient = (token) => {
  if (confirm(`Recall Token #${token.token_number} (${token.patient?.name}) into consultation queue?`)) {
    router.post(route('doctor.recall', { token: token.id }), {}, { preserveScroll: true });
  }
};

const printSlip = () => {
  window.print();
};

const filteredTokens = () => {
  if (!props.tokens) return [];
  if (activeFilter.value === 'all') return props.tokens;
  if (activeFilter.value === 'waiting') return props.tokens.filter(t => t.status === 'waiting');
  if (activeFilter.value === 'on_hold') return props.tokens.filter(t => t.status === 'on_hold');
  if (activeFilter.value === 'serving') return props.tokens.filter(t => ['calling', 'serving'].includes(t.status));
  if (activeFilter.value === 'completed') return props.tokens.filter(t => t.status === 'completed');
  return props.tokens;
};
</script>

<template>
  <Head title="Cashier & Reception Desk" />

  <div class="min-h-screen bg-slate-50 text-slate-800 selection:bg-teal-500 selection:text-white pb-20">
    <!-- Top Nav -->
    <header class="border-b border-slate-200 bg-white/90 backdrop-blur-md sticky top-0 z-40">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 rounded-2xl bg-amber-100 border border-amber-200 flex items-center justify-center text-amber-700">
            <Receipt class="w-5 h-5" />
          </div>
          <div>
            <h1 class="text-sm font-bold text-slate-900 flex items-center gap-2">
              Reception / Cashier Desk
              <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-amber-50 border border-amber-200 text-amber-700">
                Counter 01
              </span>
            </h1>
            <p class="text-[11px] text-slate-500">{{ cashier.name }}</p>
          </div>
        </div>

        <div class="flex items-center gap-3">
          <Link
            :href="route('profile.edit')"
            class="p-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 transition"
            title="Profile & Change Password"
          >
            <User class="w-4 h-4 text-slate-600" />
          </Link>

          <Link
            :href="route('display.index', { session: currentSession?.id })"
            target="_blank"
            class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold border border-slate-200 transition"
          >
            <Tv class="w-4 h-4 text-teal-600" />
            <span class="hidden sm:inline">TV Display Screen</span>
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

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-8">
      <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
        
        <!-- LEFT: WALK-IN TOKEN ISSUING FORM (COL 5) -->
        <div class="lg:col-span-5 space-y-6">
          <div class="p-6 rounded-3xl bg-white border border-slate-200 shadow-sm space-y-5">
            <div class="flex items-center justify-between border-b border-slate-100 pb-4">
              <div class="flex items-center gap-2.5">
                <div class="p-2 rounded-xl bg-teal-50 text-teal-700">
                  <UserPlus class="w-5 h-5" />
                </div>
                <div>
                  <h2 class="text-base font-bold text-slate-900">Issue Walk-In Token</h2>
                  <p class="text-xs text-slate-500">Register patient at desk</p>
                </div>
              </div>
              <span class="text-[11px] font-mono text-teal-700 bg-teal-50 px-2 py-1 rounded-md font-semibold">
                Instant Slip
              </span>
            </div>

            <!-- Active Session Selection -->
            <div class="space-y-1.5">
              <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                Clinic Session / Doctor
              </label>
              <select
                v-model="form.session_id"
                @change="switchSession(form.session_id)"
                class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3.5 py-2.5 text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:bg-white"
              >
                <option v-for="s in sessions" :key="s.id" :value="s.id">
                  {{ s.doctor?.name }} - {{ s.room_number || 'Room 01' }} ({{ s.title }})
                </option>
              </select>
            </div>

            <form @submit.prevent="issueToken" class="space-y-4">
              <!-- Mobile Phone with auto-lookup -->
              <div class="space-y-1">
                <div class="flex items-center justify-between">
                  <label class="text-xs font-semibold text-slate-700">Patient Phone</label>
                  <span v-if="isSearchingPhone" class="text-[10px] text-teal-600 animate-pulse font-medium">Searching patient...</span>
                </div>
                <div class="relative">
                  <input
                    v-model="form.phone"
                    @input="onPhoneInput"
                    type="tel"
                    required
                    placeholder="e.g. 0771234567"
                    class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3.5 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:bg-white"
                  />
                </div>
              </div>

              <!-- Patient Full Name -->
              <div class="space-y-1">
                <label class="text-xs font-semibold text-slate-700">Patient Full Name</label>
                <input
                  v-model="form.name"
                  type="text"
                  required
                  placeholder="e.g. Sunil Dharmadasa"
                  class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3.5 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:bg-white"
                />
              </div>

              <!-- Age & Gender -->
              <div class="grid grid-cols-2 gap-3">
                <div class="space-y-1">
                  <label class="text-xs font-semibold text-slate-700">Age</label>
                  <input
                    v-model="form.age"
                    type="number"
                    placeholder="e.g. 45"
                    class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3.5 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:bg-white"
                  />
                </div>

                <div class="space-y-1">
                  <label class="text-xs font-semibold text-slate-700">Gender</label>
                  <select
                    v-model="form.gender"
                    class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3.5 py-2.5 text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:bg-white"
                  >
                    <option value="Male">Male</option>
                    <option value="Female">Female</option>
                    <option value="Other">Other</option>
                  </select>
                </div>
              </div>

              <!-- Generate Token Button -->
              <button
                type="submit"
                :disabled="form.processing || !form.session_id"
                class="w-full mt-2 py-3 px-4 rounded-xl bg-teal-600 hover:bg-teal-700 text-white font-bold text-sm tracking-wide shadow-md shadow-teal-600/20 transition flex items-center justify-center gap-2 disabled:opacity-50"
              >
                <Receipt class="w-4 h-4" />
                <span>Issue & Print Walk-in Token</span>
              </button>
            </form>
          </div>
        </div>

        <!-- RIGHT: LIVE QUEUE AUDIT & RECALL DESK (COL 7) -->
        <div class="lg:col-span-7 space-y-4">
          <!-- Filter Tabs -->
          <div class="flex flex-wrap items-center justify-between gap-3 p-3 rounded-2xl bg-white border border-slate-200 shadow-xs">
            <div class="flex items-center gap-1.5">
              <button
                v-for="filter in ['all', 'waiting', 'serving', 'on_hold', 'completed']"
                :key="filter"
                @click="activeFilter = filter"
                :class="[
                  'px-3 py-1.5 rounded-xl text-xs font-semibold capitalize transition',
                  activeFilter === filter
                    ? 'bg-teal-50 text-teal-800 border border-teal-200'
                    : 'text-slate-500 hover:text-slate-800'
                ]"
              >
                {{ filter.replace('_', ' ') }}
              </button>
            </div>
            <span class="text-xs font-mono text-slate-500 font-medium">
              Total: {{ tokens?.length || 0 }}
            </span>
          </div>

          <!-- Tokens List -->
          <div class="space-y-2 max-h-[650px] overflow-y-auto pr-1">
            <div
              v-for="token in filteredTokens()"
              :key="token.id"
              class="p-4 rounded-2xl bg-white border border-slate-200 flex items-center justify-between hover:border-slate-300 transition shadow-xs"
            >
              <div class="flex items-center gap-3.5">
                <div class="w-11 h-11 rounded-xl bg-slate-50 border border-slate-200 flex items-center justify-center font-mono font-bold text-teal-700 text-sm">
                  #{{ token.token_number }}
                </div>
                <div>
                  <div class="flex items-center gap-2">
                    <h4 class="text-xs font-bold text-slate-900">{{ token.patient?.name }}</h4>
                    <span 
                      :class="[
                        'text-[10px] font-bold px-2 py-0.5 rounded-full uppercase',
                        token.status === 'serving' || token.status === 'calling' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' :
                        token.status === 'on_hold' ? 'bg-amber-50 text-amber-800 border border-amber-200' :
                        token.status === 'completed' ? 'bg-slate-100 text-slate-500' :
                        'bg-sky-50 text-sky-700 border border-sky-200'
                      ]"
                    >
                      {{ token.status.replace('_', ' ') }}
                    </span>
                  </div>
                  <p class="text-[11px] text-slate-500 mt-0.5">
                    {{ token.patient?.phone }} &bull; Source: <span class="capitalize">{{ token.type }}</span>
                  </p>
                </div>
              </div>

              <!-- Action: If On-Hold, Cashier can Recall! -->
              <div class="flex items-center gap-2">
                <button
                  v-if="token.status === 'on_hold'"
                  @click="recallPatient(token)"
                  class="px-3 py-1.5 rounded-xl bg-amber-500 hover:bg-amber-600 text-white font-bold text-xs transition flex items-center gap-1 shadow-xs"
                >
                  <RotateCcw class="w-3.5 h-3.5" />
                  <span>Recall Patient</span>
                </button>

                <button
                  @click="printTokenData = token; showPrintModal = true"
                  class="p-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 transition"
                  title="Print Slip"
                >
                  <Printer class="w-3.5 h-3.5" />
                </button>
              </div>
            </div>

            <div v-if="filteredTokens().length === 0" class="p-8 rounded-2xl bg-white border border-slate-200 text-center text-xs text-slate-400">
              No tokens found matching this filter.
            </div>
          </div>
        </div>

      </div>
    </main>

    <!-- PRINTABLE TOKEN SLIP MODAL -->
    <div
      v-if="showPrintModal && printTokenData"
      class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-50 flex items-center justify-center p-4"
    >
      <div class="bg-white text-slate-900 rounded-3xl p-6 sm:p-8 max-w-sm w-full shadow-2xl relative space-y-6 border border-slate-200">
        <button
          @click="showPrintModal = false"
          class="absolute top-4 right-4 p-2 text-slate-400 hover:text-slate-600 rounded-full"
        >
          <X class="w-5 h-5" />
        </button>

        <!-- Printable Slip Area -->
        <div id="printable-slip" class="text-center space-y-4 font-mono border-2 border-dashed border-slate-300 p-5 rounded-2xl bg-slate-50/50">
          <div class="space-y-1">
            <h3 class="text-sm font-black uppercase tracking-wider text-slate-800">CareQueue Clinic</h3>
            <p class="text-[11px] text-slate-500">Queue Token Slip</p>
          </div>

          <div class="py-2 border-y border-dashed border-slate-300">
            <span class="text-[10px] uppercase text-slate-500 tracking-wider font-semibold">Your Token Number</span>
            <div class="text-5xl font-black text-teal-800 py-1">
              #{{ printTokenData.token_number }}
            </div>
            <p class="text-xs font-bold text-slate-800">{{ printTokenData.patient?.name }}</p>
          </div>

          <div class="text-left text-[11px] space-y-1 text-slate-600">
            <p><strong>Doctor:</strong> {{ currentSession?.doctor?.name || 'Doctor' }}</p>
            <p><strong>Room:</strong> {{ currentSession?.room_number || 'Room 01' }}</p>
            <p><strong>Date:</strong> {{ new Date().toLocaleDateString() }} - {{ new Date().toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'}) }}</p>
          </div>

          <p class="text-[10px] text-slate-500 italic">
            Please watch the TV Waiting screen. If you miss your number, inform the cashier.
          </p>
        </div>

        <div class="flex gap-3">
          <button
            @click="printSlip"
            class="flex-1 py-3 px-4 rounded-xl bg-teal-600 hover:bg-teal-700 text-white font-bold text-xs transition flex items-center justify-center gap-2 shadow-sm"
          >
            <Printer class="w-4 h-4" />
            <span>Print Thermal Slip</span>
          </button>
          <button
            @click="showPrintModal = false"
            class="px-4 py-3 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition"
          >
            Close
          </button>
        </div>
      </div>
    </div>
  </div>
</template>
