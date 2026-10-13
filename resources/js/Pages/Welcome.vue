<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { 
  Stethoscope, 
  Tv, 
  Receipt, 
  Calendar, 
  Clock, 
  UserCheck, 
  Smartphone, 
  ArrowRight, 
  Sparkles,
  ShieldCheck,
  Search,
  CheckCircle2,
  AlertCircle
} from 'lucide-vue-next';

const props = defineProps({
  sessions: {
    type: Array,
    default: () => []
  }
});

const selectedSessionId = ref(props.sessions[0]?.id || null);

const form = useForm({
  session_id: selectedSessionId.value,
  name: '',
  phone: '',
  age: '',
  gender: 'Male',
});

const submitBooking = () => {
  form.session_id = selectedSessionId.value;
  form.post(route('patient.book'), {
    preserveScroll: true,
  });
};

const searchTokenId = ref('');
const trackToken = () => {
  if (searchTokenId.value.trim()) {
    window.location.href = route('patient.tracker', { token: searchTokenId.value.trim() });
  }
};
</script>

<template>
  <Head title="Clinic Queue Management & Online Token Booking" />

  <div class="min-h-screen bg-slate-50 text-slate-800 selection:bg-teal-500 selection:text-white">
    <!-- Top Navigation Bar -->
    <header class="border-b border-slate-200/80 bg-white/90 backdrop-blur-md sticky top-0 z-50">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-20 flex items-center justify-between">
        <div class="flex items-center space-x-3">
          <div class="h-11 w-11 rounded-2xl bg-teal-600 flex items-center justify-center shadow-md shadow-teal-600/20 text-white">
            <Stethoscope class="w-6 h-6" />
          </div>
          <div>
            <span class="text-xl font-bold tracking-tight text-slate-900 flex items-center gap-1.5">
              CareQueue <span class="text-teal-700 text-xs px-2 py-0.5 rounded-full bg-teal-50 border border-teal-200 font-medium">Healthcare</span>
            </span>
            <p class="text-xs text-slate-500">Doctor Queue & Clinic Portal</p>
          </div>
        </div>

        <!-- Quick Access Shortcuts -->
        <div class="flex items-center gap-2 sm:gap-4">
          <Link
            :href="route('display.index')"
            target="_blank"
            class="hidden md:inline-flex items-center gap-2 px-3.5 py-2 text-xs font-semibold rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-200 transition shadow-sm"
          >
            <Tv class="w-4 h-4 text-teal-600" />
            <span>TV Waiting Display</span>
          </Link>

          <Link
            :href="route('login')"
            class="inline-flex items-center gap-2 px-4 py-2 text-xs font-semibold rounded-xl bg-teal-600 hover:bg-teal-700 text-white transition shadow-sm"
          >
            <span>Staff Login</span>
            <ArrowRight class="w-3.5 h-3.5" />
          </Link>
        </div>
      </div>
    </header>

    <!-- Hero & Booking Section -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 lg:py-14">
      <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-start">
        
        <!-- Left Column: Information & Track by ID -->
        <div class="lg:col-span-6 space-y-8">
          <div class="space-y-4">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-teal-100/80 border border-teal-200 text-teal-800 text-xs font-semibold">
              <Sparkles class="w-3.5 h-3.5 text-teal-600" />
              <span>Smart Hospital Patient Flow</span>
            </div>
            <h1 class="text-4xl sm:text-5xl font-extrabold text-slate-900 tracking-tight leading-tight">
              Get Your Doctor <span class="text-teal-600">Queue Token</span> In Seconds
            </h1>
            <p class="text-slate-600 text-base sm:text-lg leading-relaxed">
              No more standing in crowded waiting rooms. Book an online token from home, monitor live queue progress directly on your smartphone, and arrive just in time.
            </p>
          </div>

          <!-- Quick Track Box -->
          <div class="p-6 rounded-2xl bg-white border border-slate-200 shadow-sm relative overflow-hidden">
            <h3 class="text-sm font-bold text-slate-800 flex items-center gap-2 mb-3">
              <Smartphone class="w-4 h-4 text-teal-600" />
              Already have a Token? Check Live Status
            </h3>
            <div class="flex gap-2">
              <div class="relative flex-1">
                <Search class="w-4 h-4 absolute left-3 top-3 text-slate-400" />
                <input
                  v-model="searchTokenId"
                  type="number"
                  placeholder="Enter Token ID (e.g. 3)"
                  class="w-full bg-slate-50 border border-slate-300 rounded-xl pl-9 pr-3 py-2 text-sm text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:bg-white"
                  @keyup.enter="trackToken"
                />
              </div>
              <button
                @click="trackToken"
                class="px-4 py-2 bg-teal-50 hover:bg-teal-100 text-teal-700 border border-teal-200 font-semibold text-xs rounded-xl transition flex items-center gap-1.5"
              >
                Track Now
              </button>
            </div>
          </div>

          <!-- Feature Badges -->
          <div class="grid grid-cols-2 gap-4 pt-2">
            <div class="p-4 rounded-xl bg-white border border-slate-200 flex items-start gap-3 shadow-xs">
              <CheckCircle2 class="w-5 h-5 text-teal-600 shrink-0 mt-0.5" />
              <div>
                <h4 class="text-xs font-bold text-slate-900">Live Real-time Sync</h4>
                <p class="text-[11px] text-slate-500 mt-0.5">Instant updates when doctor calls the next patient.</p>
              </div>
            </div>
            <div class="p-4 rounded-xl bg-white border border-slate-200 flex items-start gap-3 shadow-xs">
              <ShieldCheck class="w-5 h-5 text-cyan-600 shrink-0 mt-0.5" />
              <div>
                <h4 class="text-xs font-bold text-slate-900">Missed Turn Protection</h4>
                <p class="text-[11px] text-slate-500 mt-0.5">Arrived late? Token stays on hold for easy recall.</p>
              </div>
            </div>
          </div>

          <!-- Staff Direct Portal Quick Links -->
          <div class="pt-4 border-t border-slate-200">
            <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-3">Clinic Staff Portals</p>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
              <Link
                :href="route('doctor.dashboard')"
                class="p-3.5 rounded-xl bg-white hover:bg-slate-100/80 border border-slate-200 flex flex-col gap-1 transition group shadow-xs"
              >
                <div class="flex items-center justify-between">
                  <Stethoscope class="w-4 h-4 text-emerald-600" />
                  <span class="text-[10px] text-emerald-700 font-semibold bg-emerald-50 px-1.5 py-0.5 rounded">Doctor</span>
                </div>
                <span class="text-xs font-bold text-slate-800 group-hover:text-emerald-700">Doctor Console</span>
                <span class="text-[11px] text-slate-500">Call, skip & complete</span>
              </Link>

              <Link
                :href="route('cashier.dashboard')"
                class="p-3.5 rounded-xl bg-white hover:bg-slate-100/80 border border-slate-200 flex flex-col gap-1 transition group shadow-xs"
              >
                <div class="flex items-center justify-between">
                  <Receipt class="w-4 h-4 text-amber-600" />
                  <span class="text-[10px] text-amber-700 font-semibold bg-amber-50 px-1.5 py-0.5 rounded">Walk-in</span>
                </div>
                <span class="text-xs font-bold text-slate-800 group-hover:text-amber-700">Cashier Desk</span>
                <span class="text-[11px] text-slate-500">Fast token generation</span>
              </Link>

              <Link
                :href="route('display.index')"
                class="p-3.5 rounded-xl bg-white hover:bg-slate-100/80 border border-slate-200 flex flex-col gap-1 transition group shadow-xs"
              >
                <div class="flex items-center justify-between">
                  <Tv class="w-4 h-4 text-cyan-600" />
                  <span class="text-[10px] text-cyan-700 font-semibold bg-cyan-50 px-1.5 py-0.5 rounded">Display</span>
                </div>
                <span class="text-xs font-bold text-slate-800 group-hover:text-cyan-700">Waiting Screen</span>
                <span class="text-[11px] text-slate-500">Audio call & visual sync</span>
              </Link>
            </div>
          </div>
        </div>

        <!-- Right Column: Online Token Reservation Card -->
        <div class="lg:col-span-6">
          <div class="p-6 sm:p-8 rounded-3xl bg-white border border-slate-200 shadow-xl shadow-slate-200/50 relative">
            <div class="flex items-center justify-between mb-6">
              <div>
                <h2 class="text-xl font-bold text-slate-900">Book Online Token</h2>
                <p class="text-xs text-slate-500 mt-1">Select doctor and reserve your appointment slot</p>
              </div>
              <span class="px-3 py-1 text-xs font-semibold rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200 flex items-center gap-1.5">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                Clinic Open
              </span>
            </div>

            <!-- If no active sessions -->
            <div v-if="sessions.length === 0" class="p-6 rounded-2xl bg-slate-50 border border-slate-200 text-center space-y-3">
              <AlertCircle class="w-8 h-8 text-amber-500 mx-auto" />
              <p class="text-sm text-slate-700 font-medium">No active clinic sessions scheduled for today.</p>
              <p class="text-xs text-slate-500">Please check back during regular clinic hours or log in as staff to start a session.</p>
            </div>

            <form v-else @submit.prevent="submitBooking" class="space-y-5">
              <!-- Select Session / Doctor -->
              <div class="space-y-2">
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                  Select Doctor / Session
                </label>
                <div class="space-y-2.5">
                  <div
                    v-for="s in sessions"
                    :key="s.id"
                    @click="selectedSessionId = s.id"
                    :class="[
                      'p-4 rounded-2xl border cursor-pointer transition flex items-center justify-between',
                      selectedSessionId === s.id
                        ? 'bg-teal-50/70 border-teal-500 shadow-sm ring-1 ring-teal-500/20'
                        : 'bg-white border-slate-200 hover:border-slate-300'
                    ]"
                  >
                    <div class="flex items-center gap-3.5">
                      <div class="w-10 h-10 rounded-xl bg-teal-100/70 text-teal-800 flex items-center justify-center font-bold text-xs">
                        {{ s.room_number || 'R1' }}
                      </div>
                      <div>
                        <h4 class="text-sm font-bold text-slate-900">{{ s.doctor?.name || 'Doctor' }}</h4>
                        <p class="text-xs text-slate-500">{{ s.doctor?.specialization || 'Consultant' }}</p>
                      </div>
                    </div>
                    <div class="text-right">
                      <span class="text-[11px] font-semibold text-teal-700 bg-teal-100/80 px-2.5 py-1 rounded-lg">
                        {{ s.title }}
                      </span>
                    </div>
                  </div>
                </div>
                <div v-if="form.errors.session_id" class="text-xs text-rose-500 mt-1">
                  {{ form.errors.session_id }}
                </div>
              </div>

              <!-- Patient Name -->
              <div class="space-y-1.5">
                <label class="block text-xs font-semibold text-slate-700">Patient Full Name</label>
                <input
                  v-model="form.name"
                  type="text"
                  required
                  placeholder="e.g. Nimal Perera"
                  class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:bg-white"
                />
                <div v-if="form.errors.name" class="text-xs text-rose-500">{{ form.errors.name }}</div>
              </div>

              <!-- Phone & Age -->
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="space-y-1.5">
                  <label class="block text-xs font-semibold text-slate-700">Mobile Phone Number</label>
                  <input
                    v-model="form.phone"
                    type="tel"
                    required
                    placeholder="e.g. 0771234567"
                    class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:bg-white"
                  />
                  <div v-if="form.errors.phone" class="text-xs text-rose-500">{{ form.errors.phone }}</div>
                </div>

                <div class="space-y-1.5">
                  <label class="block text-xs font-semibold text-slate-700">Age</label>
                  <input
                    v-model="form.age"
                    type="number"
                    min="1"
                    max="120"
                    placeholder="e.g. 35"
                    class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:bg-white"
                  />
                  <div v-if="form.errors.age" class="text-xs text-rose-500">{{ form.errors.age }}</div>
                </div>
              </div>

              <!-- Gender -->
              <div class="space-y-1.5">
                <label class="block text-xs font-semibold text-slate-700">Gender</label>
                <div class="grid grid-cols-3 gap-2">
                  <button
                    type="button"
                    v-for="gender in ['Male', 'Female', 'Other']"
                    :key="gender"
                    @click="form.gender = gender"
                    :class="[
                      'py-2 text-xs font-semibold rounded-xl border transition',
                      form.gender === gender
                        ? 'bg-teal-50 border-teal-500 text-teal-800 font-bold'
                        : 'bg-white border-slate-200 text-slate-600 hover:border-slate-300'
                    ]"
                  >
                    {{ gender }}
                  </button>
                </div>
              </div>

              <!-- Submit Button -->
              <button
                type="submit"
                :disabled="form.processing || !selectedSessionId"
                class="w-full mt-2 py-3.5 px-6 rounded-2xl bg-teal-600 hover:bg-teal-700 text-white font-bold text-sm tracking-wide shadow-md shadow-teal-600/20 transition disabled:opacity-50 flex items-center justify-center gap-2"
              >
                <span v-if="form.processing">Processing Token...</span>
                <span v-else>Confirm & Get Token Number</span>
                <ArrowRight class="w-4 h-4" />
              </button>

              <p class="text-[11px] text-center text-slate-500">
                You will receive a digital token code with real-time live tracking.
              </p>
            </form>
          </div>
        </div>

      </div>
    </main>

    <!-- Footer -->
    <footer class="border-t border-slate-200 mt-20 py-8 text-center text-xs text-slate-500">
      <p>&copy; 2026 CareQueue Clinic Management System. All rights reserved.</p>
    </footer>
  </div>
</template>
