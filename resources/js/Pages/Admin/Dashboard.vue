<script setup>
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { 
  ShieldCheck, 
  Stethoscope, 
  Calendar, 
  Plus, 
  Users, 
  Tv, 
  LogOut, 
  CheckCircle2, 
  Clock, 
  AlertCircle, 
  Receipt, 
  Activity, 
  Eye, 
  PauseCircle, 
  Play, 
  Check, 
  X,
  Phone,
  Mail,
  UserCheck,
  UserX,
  User
} from 'lucide-vue-next';

const props = defineProps({
  doctors: Array,
  cashiers: Array,
  sessions: Array,
  stats: Object,
});

const activeTab = ref('sessions'); // 'sessions', 'doctors', 'cashiers'

// Modal States
const showSessionModal = ref(false);
const showDoctorModal = ref(false);
const showCashierModal = ref(false);

// Session Form
const sessionForm = useForm({
  doctor_id: props.doctors[0]?.id || '',
  session_date: new Date().toISOString().split('T')[0],
  title: 'General Consultation',
  room_number: props.doctors[0]?.room_number || 'Room 01',
  max_tokens: 50,
  status: 'active',
});

// Update room number automatically when doctor changes in form
const onDoctorSelect = () => {
  const selectedDoc = props.doctors.find(d => d.id == sessionForm.doctor_id);
  if (selectedDoc?.room_number) {
    sessionForm.room_number = selectedDoc.room_number;
  }
};

const createSession = () => {
  sessionForm.post(route('admin.sessions.store'), {
    preserveScroll: true,
    onSuccess: () => {
      showSessionModal.value = false;
      sessionForm.reset('title');
    },
  });
};

const updateSessionStatus = (session, newStatus) => {
  router.post(route('admin.sessions.status', { session: session.id }), {
    status: newStatus,
  }, {
    preserveScroll: true,
  });
};

// Doctor Form
const doctorForm = useForm({
  name: '',
  email: '',
  password: 'password',
  room_number: 'Room 01',
  specialization: 'General Practitioner',
  phone: '',
});

const createDoctor = () => {
  doctorForm.post(route('admin.doctors.store'), {
    preserveScroll: true,
    onSuccess: () => {
      showDoctorModal.value = false;
      doctorForm.reset('name', 'email', 'phone');
    },
  });
};

// Cashier Form
const cashierForm = useForm({
  name: '',
  email: '',
  password: 'password',
  room_number: 'Counter 01',
  phone: '',
});

const createCashier = () => {
  cashierForm.post(route('admin.cashiers.store'), {
    preserveScroll: true,
    onSuccess: () => {
      showCashierModal.value = false;
      cashierForm.reset('name', 'email', 'phone');
    },
  });
};

// Toggle Staff active/inactive
const toggleStaff = (user) => {
  router.post(route('admin.staff.toggle', { user: user.id }), {}, {
    preserveScroll: true,
  });
};
</script>

<template>
  <Head title="Admin Console - Clinic Management" />

  <div class="min-h-screen bg-slate-50 text-slate-800 selection:bg-teal-500 selection:text-white pb-20">
    <!-- Top Admin Header -->
    <header class="border-b border-slate-200 bg-white/95 backdrop-blur-md sticky top-0 z-40">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 rounded-2xl bg-teal-600 flex items-center justify-center text-white shadow-md shadow-teal-600/20">
            <ShieldCheck class="w-5 h-5" />
          </div>
          <div>
            <h1 class="text-sm font-bold text-slate-900 flex items-center gap-2">
              Clinic Administration Console
              <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-teal-50 border border-teal-200 text-teal-800">
                Admin
              </span>
            </h1>
            <p class="text-[11px] text-slate-500">Manage Doctors, Sessions & Staff</p>
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
            :href="route('display.index')"
            target="_blank"
            class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold border border-slate-200 transition"
          >
            <Tv class="w-4 h-4 text-teal-600" />
            <span class="hidden sm:inline">TV Display Screen</span>
          </Link>

          <Link
            :href="route('home')"
            target="_blank"
            class="hidden md:flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold border border-slate-200 transition"
          >
            <Eye class="w-4 h-4 text-cyan-600" />
            <span>Patient Portal</span>
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

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-8 space-y-8">
      
      <!-- Top Metrics Ribbon -->
      <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="p-5 rounded-2xl bg-white border border-slate-200 shadow-xs flex items-center gap-4">
          <div class="w-12 h-12 rounded-xl bg-teal-50 border border-teal-200 flex items-center justify-center text-teal-700">
            <Stethoscope class="w-6 h-6" />
          </div>
          <div>
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Doctors</span>
            <span class="text-2xl font-black text-slate-900 font-mono">{{ stats.doctors_count }}</span>
          </div>
        </div>

        <div class="p-5 rounded-2xl bg-white border border-slate-200 shadow-xs flex items-center gap-4">
          <div class="w-12 h-12 rounded-xl bg-emerald-50 border border-emerald-200 flex items-center justify-center text-emerald-700">
            <Activity class="w-6 h-6" />
          </div>
          <div>
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Active Sessions</span>
            <span class="text-2xl font-black text-slate-900 font-mono">{{ stats.active_sessions_count }}</span>
          </div>
        </div>

        <div class="p-5 rounded-2xl bg-white border border-slate-200 shadow-xs flex items-center gap-4">
          <div class="w-12 h-12 rounded-xl bg-cyan-50 border border-cyan-200 flex items-center justify-center text-cyan-700">
            <Users class="w-6 h-6" />
          </div>
          <div>
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Today's Tokens</span>
            <span class="text-2xl font-black text-slate-900 font-mono">{{ stats.today_tokens }}</span>
          </div>
        </div>

        <div class="p-5 rounded-2xl bg-white border border-slate-200 shadow-xs flex items-center gap-4">
          <div class="w-12 h-12 rounded-xl bg-amber-50 border border-amber-200 flex items-center justify-center text-amber-700">
            <CheckCircle2 class="w-6 h-6" />
          </div>
          <div>
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Consulted Today</span>
            <span class="text-2xl font-black text-slate-900 font-mono">{{ stats.today_completed }}</span>
          </div>
        </div>
      </div>

      <!-- Navigation Tabs & Action Buttons -->
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-200 pb-4">
        <!-- Tabs -->
        <div class="flex items-center gap-2 bg-slate-200/70 p-1.5 rounded-2xl border border-slate-300/60">
          <button
            @click="activeTab = 'sessions'"
            :class="[
              'px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-2',
              activeTab === 'sessions'
                ? 'bg-white text-teal-800 shadow-xs'
                : 'text-slate-600 hover:text-slate-900'
            ]"
          >
            <Calendar class="w-4 h-4" />
            <span>Clinic Sessions ({{ sessions.length }})</span>
          </button>

          <button
            @click="activeTab = 'doctors'"
            :class="[
              'px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-2',
              activeTab === 'doctors'
                ? 'bg-white text-teal-800 shadow-xs'
                : 'text-slate-600 hover:text-slate-900'
            ]"
          >
            <Stethoscope class="w-4 h-4" />
            <span>Doctors ({{ doctors.length }})</span>
          </button>

          <button
            @click="activeTab = 'cashiers'"
            :class="[
              'px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-2',
              activeTab === 'cashiers'
                ? 'bg-white text-teal-800 shadow-xs'
                : 'text-slate-600 hover:text-slate-900'
            ]"
          >
            <Receipt class="w-4 h-4" />
            <span>Reception Staff ({{ cashiers.length }})</span>
          </button>
        </div>

        <!-- Primary Action Button based on Tab -->
        <div>
          <button
            v-if="activeTab === 'sessions'"
            @click="showSessionModal = true"
            class="px-4 py-2.5 rounded-xl bg-teal-600 hover:bg-teal-700 text-white font-bold text-xs transition flex items-center gap-2 shadow-sm"
          >
            <Plus class="w-4 h-4" />
            <span>Create Clinic Session</span>
          </button>

          <button
            v-else-if="activeTab === 'doctors'"
            @click="showDoctorModal = true"
            class="px-4 py-2.5 rounded-xl bg-teal-600 hover:bg-teal-700 text-white font-bold text-xs transition flex items-center gap-2 shadow-sm"
          >
            <Plus class="w-4 h-4" />
            <span>Register New Doctor</span>
          </button>

          <button
            v-else-if="activeTab === 'cashiers'"
            @click="showCashierModal = true"
            class="px-4 py-2.5 rounded-xl bg-teal-600 hover:bg-teal-700 text-white font-bold text-xs transition flex items-center gap-2 shadow-sm"
          >
            <Plus class="w-4 h-4" />
            <span>Register Reception Staff</span>
          </button>
        </div>
      </div>

      <!-- TAB 1: CLINIC SESSIONS TABLE / LIST -->
      <div v-if="activeTab === 'sessions'" class="space-y-4">
        <div v-if="sessions.length === 0" class="p-12 text-center bg-white rounded-3xl border border-slate-200">
          <Calendar class="w-12 h-12 text-slate-300 mx-auto mb-3" />
          <h3 class="text-base font-bold text-slate-700">No Clinic Sessions Created Yet</h3>
          <p class="text-xs text-slate-500 mt-1">Click "Create Clinic Session" above to assign a doctor and open patient queues.</p>
        </div>

        <div v-else class="grid grid-cols-1 gap-3">
          <div
            v-for="session in sessions"
            :key="session.id"
            class="p-5 rounded-2xl bg-white border border-slate-200 hover:border-slate-300 transition flex flex-col md:flex-row md:items-center justify-between gap-4 shadow-xs"
          >
            <!-- Doctor & Session Info -->
            <div class="flex items-start gap-4">
              <div class="h-11 px-3.5 rounded-xl bg-teal-50 border border-teal-200 flex items-center justify-center text-teal-800 font-bold font-mono text-xs whitespace-nowrap shrink-0 shadow-xs">
                {{ session.room_number || 'Room 01' }}
              </div>
              <div class="space-y-1">
                <div class="flex items-center gap-2 flex-wrap">
                  <h3 class="text-sm font-bold text-slate-900">{{ session.title }}</h3>
                  <span
                    :class="[
                      'px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider',
                      session.status === 'active' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' :
                      session.status === 'scheduled' ? 'bg-sky-50 text-sky-700 border border-sky-200' :
                      session.status === 'paused' ? 'bg-amber-50 text-amber-700 border border-amber-200' :
                      'bg-slate-100 text-slate-500 border border-slate-200'
                    ]"
                  >
                    {{ session.status }}
                  </span>
                </div>
                <p class="text-xs text-slate-600 font-medium">
                  <strong>Doctor:</strong> {{ session.doctor?.name }} &bull; {{ session.doctor?.specialization || 'Consultant' }}
                </p>
                <p class="text-[11px] text-slate-400">
                  Date: {{ session.session_date }} &bull; Max Limit: {{ session.max_tokens }} Tokens
                </p>
              </div>
            </div>

            <!-- Queue Progress Numbers -->
            <div class="flex items-center gap-4 bg-slate-50 p-3 rounded-xl border border-slate-100 text-center text-xs">
              <div>
                <span class="text-[10px] uppercase text-slate-400 block font-bold">Total</span>
                <span class="font-bold text-slate-900 font-mono">{{ session.total_tokens || 0 }}</span>
              </div>
              <div class="border-l border-slate-200 pl-3">
                <span class="text-[10px] uppercase text-teal-600 block font-bold">Waiting</span>
                <span class="font-bold text-teal-700 font-mono">{{ session.waiting_tokens || 0 }}</span>
              </div>
              <div class="border-l border-slate-200 pl-3">
                <span class="text-[10px] uppercase text-emerald-600 block font-bold">Served</span>
                <span class="font-bold text-emerald-700 font-mono">{{ session.completed_tokens || 0 }}</span>
              </div>
            </div>

            <!-- Action Controls -->
            <div class="flex items-center gap-2 shrink-0">
              <Link
                :href="route('display.index', { session: session.id })"
                target="_blank"
                class="px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs transition flex items-center gap-1.5 border border-slate-200"
                title="Open Dedicated TV Display"
              >
                <Tv class="w-3.5 h-3.5 text-teal-600" />
                <span>TV Screen</span>
              </Link>

              <!-- Status Switcher -->
              <div class="flex items-center gap-1">
                <button
                  v-if="session.status !== 'active' && session.status !== 'completed'"
                  @click="updateSessionStatus(session, 'active')"
                  class="px-2.5 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs transition flex items-center gap-1"
                >
                  <Play class="w-3 h-3 fill-white" />
                  <span>Activate</span>
                </button>

                <button
                  v-if="session.status === 'active'"
                  @click="updateSessionStatus(session, 'paused')"
                  class="px-2.5 py-1.5 rounded-xl bg-amber-500 hover:bg-amber-600 text-white font-semibold text-xs transition flex items-center gap-1"
                >
                  <PauseCircle class="w-3.5 h-3.5" />
                  <span>Pause</span>
                </button>

                <button
                  v-if="session.status !== 'completed'"
                  @click="updateSessionStatus(session, 'completed')"
                  class="px-2.5 py-1.5 rounded-xl bg-slate-200 hover:bg-slate-300 text-slate-700 font-semibold text-xs transition flex items-center gap-1"
                >
                  <Check class="w-3.5 h-3.5" />
                  <span>End Session</span>
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- TAB 2: DOCTORS LIST -->
      <div v-else-if="activeTab === 'doctors'" class="space-y-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
          <div
            v-for="doc in doctors"
            :key="doc.id"
            class="p-5 rounded-2xl bg-white border border-slate-200 shadow-xs space-y-4"
          >
            <div class="flex items-start justify-between">
              <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-2xl bg-teal-50 border border-teal-200 flex items-center justify-center text-teal-700 font-bold">
                  <Stethoscope class="w-6 h-6" />
                </div>
                <div>
                  <h4 class="text-sm font-bold text-slate-900">{{ doc.name }}</h4>
                  <p class="text-xs text-slate-500">{{ doc.specialization || 'Doctor' }}</p>
                </div>
              </div>

              <span
                :class="[
                  'px-2 py-0.5 rounded-full text-[10px] font-bold',
                  doc.is_active ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-slate-100 text-slate-400'
                ]"
              >
                {{ doc.is_active ? 'Active' : 'Inactive' }}
              </span>
            </div>

            <div class="space-y-1.5 text-xs text-slate-600 pt-2 border-t border-slate-100">
              <div class="flex items-center gap-2">
                <Mail class="w-3.5 h-3.5 text-slate-400" />
                <span>{{ doc.email }}</span>
              </div>
              <div class="flex items-center gap-2">
                <Phone class="w-3.5 h-3.5 text-slate-400" />
                <span>{{ doc.phone || 'No phone set' }}</span>
              </div>
              <div class="flex items-center justify-between text-[11px] text-slate-400 pt-1">
                <span>Room: <strong class="text-slate-800">{{ doc.room_number || 'Room 01' }}</strong></span>
                <span>Sessions: <strong class="text-slate-800">{{ doc.clinic_sessions_count || 0 }}</strong></span>
              </div>
            </div>

            <div class="pt-2">
              <button
                @click="toggleStaff(doc)"
                :class="[
                  'w-full py-1.5 px-3 rounded-xl text-xs font-semibold border transition flex items-center justify-center gap-1.5',
                  doc.is_active
                    ? 'border-slate-200 text-slate-600 hover:bg-slate-100'
                    : 'bg-emerald-50 border-emerald-200 text-emerald-700 hover:bg-emerald-100'
                ]"
              >
                <component :is="doc.is_active ? UserX : UserCheck" class="w-3.5 h-3.5" />
                <span>{{ doc.is_active ? 'Deactivate Doctor' : 'Activate Doctor' }}</span>
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- TAB 3: CASHIERS & RECEPTION STAFF LIST -->
      <div v-else-if="activeTab === 'cashiers'" class="space-y-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
          <div
            v-for="cashier in cashiers"
            :key="cashier.id"
            class="p-5 rounded-2xl bg-white border border-slate-200 shadow-xs space-y-4"
          >
            <div class="flex items-start justify-between">
              <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-2xl bg-amber-50 border border-amber-200 flex items-center justify-center text-amber-700 font-bold">
                  <Receipt class="w-6 h-6" />
                </div>
                <div>
                  <h4 class="text-sm font-bold text-slate-900">{{ cashier.name }}</h4>
                  <p class="text-xs text-slate-500">Receptionist / Cashier</p>
                </div>
              </div>

              <span
                :class="[
                  'px-2 py-0.5 rounded-full text-[10px] font-bold',
                  cashier.is_active ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-slate-100 text-slate-400'
                ]"
              >
                {{ cashier.is_active ? 'Active' : 'Inactive' }}
              </span>
            </div>

            <div class="space-y-1.5 text-xs text-slate-600 pt-2 border-t border-slate-100">
              <div class="flex items-center gap-2">
                <Mail class="w-3.5 h-3.5 text-slate-400" />
                <span>{{ cashier.email }}</span>
              </div>
              <div class="flex items-center gap-2">
                <Phone class="w-3.5 h-3.5 text-slate-400" />
                <span>{{ cashier.phone || 'No phone set' }}</span>
              </div>
              <div class="text-[11px] text-slate-400 pt-1">
                Assigned: <strong class="text-slate-800">{{ cashier.room_number || 'Counter 01' }}</strong>
              </div>
            </div>

            <div class="pt-2">
              <button
                @click="toggleStaff(cashier)"
                :class="[
                  'w-full py-1.5 px-3 rounded-xl text-xs font-semibold border transition flex items-center justify-center gap-1.5',
                  cashier.is_active
                    ? 'border-slate-200 text-slate-600 hover:bg-slate-100'
                    : 'bg-emerald-50 border-emerald-200 text-emerald-700 hover:bg-emerald-100'
                ]"
              >
                <component :is="cashier.is_active ? UserX : UserCheck" class="w-3.5 h-3.5" />
                <span>{{ cashier.is_active ? 'Deactivate Staff' : 'Activate Staff' }}</span>
              </button>
            </div>
          </div>
        </div>
      </div>

    </main>

    <!-- MODAL 1: CREATE CLINIC SESSION -->
    <div
      v-if="showSessionModal"
      class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-50 flex items-center justify-center p-4"
    >
      <div class="bg-white rounded-3xl p-6 sm:p-8 max-w-md w-full shadow-2xl relative space-y-5 border border-slate-200">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
          <div class="flex items-center gap-2 text-teal-800 font-bold text-sm">
            <Calendar class="w-5 h-5 text-teal-600" />
            <span>Create New Clinic Session</span>
          </div>
          <button @click="showSessionModal = false" class="p-1 rounded-full text-slate-400 hover:text-slate-600">
            <X class="w-5 h-5" />
          </button>
        </div>

        <form @submit.prevent="createSession" class="space-y-4">
          <!-- Doctor Selection -->
          <div class="space-y-1">
            <label class="block text-xs font-bold text-slate-700">Assign Doctor</label>
            <select
              v-model="sessionForm.doctor_id"
              @change="onDoctorSelect"
              required
              class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:bg-white"
            >
              <option v-for="d in doctors" :key="d.id" :value="d.id">
                {{ d.name }} ({{ d.room_number || 'Room 01' }}) - {{ d.specialization }}
              </option>
            </select>
            <div v-if="sessionForm.errors.doctor_id" class="text-xs text-rose-500">{{ sessionForm.errors.doctor_id }}</div>
          </div>

          <!-- Session Title -->
          <div class="space-y-1">
            <label class="block text-xs font-semibold text-slate-700">Session Title</label>
            <input
              v-model="sessionForm.title"
              type="text"
              required
              placeholder="e.g. Morning OPD Consultation"
              class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:bg-white"
            />
          </div>

          <!-- Date & Room Number -->
          <div class="grid grid-cols-2 gap-3">
            <div class="space-y-1">
              <label class="block text-xs font-semibold text-slate-700">Session Date</label>
              <input
                v-model="sessionForm.session_date"
                type="date"
                required
                class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:bg-white"
              />
            </div>

            <div class="space-y-1">
              <label class="block text-xs font-semibold text-slate-700">Room Number</label>
              <input
                v-model="sessionForm.room_number"
                type="text"
                placeholder="e.g. Room 02"
                class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:bg-white"
              />
            </div>
          </div>

          <!-- Max Tokens & Initial Status -->
          <div class="grid grid-cols-2 gap-3">
            <div class="space-y-1">
              <label class="block text-xs font-semibold text-slate-700">Max Tokens Limit</label>
              <input
                v-model="sessionForm.max_tokens"
                type="number"
                min="5"
                max="500"
                required
                class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:bg-white"
              />
            </div>

            <div class="space-y-1">
              <label class="block text-xs font-semibold text-slate-700">Initial Status</label>
              <select
                v-model="sessionForm.status"
                class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:bg-white"
              >
                <option value="active">Active (Open Now)</option>
                <option value="scheduled">Scheduled (Later)</option>
              </select>
            </div>
          </div>

          <div class="pt-2 flex gap-3">
            <button
              type="submit"
              :disabled="sessionForm.processing"
              class="flex-1 py-2.5 px-4 rounded-xl bg-teal-600 hover:bg-teal-700 text-white font-bold text-xs transition shadow-sm"
            >
              Save & Open Session
            </button>
            <button
              type="button"
              @click="showSessionModal = false"
              class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold transition"
            >
              Cancel
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- MODAL 2: REGISTER NEW DOCTOR -->
    <div
      v-if="showDoctorModal"
      class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-50 flex items-center justify-center p-4"
    >
      <div class="bg-white rounded-3xl p-6 sm:p-8 max-w-md w-full shadow-2xl relative space-y-5 border border-slate-200">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
          <div class="flex items-center gap-2 text-teal-800 font-bold text-sm">
            <Stethoscope class="w-5 h-5 text-teal-600" />
            <span>Register New Doctor</span>
          </div>
          <button @click="showDoctorModal = false" class="p-1 rounded-full text-slate-400 hover:text-slate-600">
            <X class="w-5 h-5" />
          </button>
        </div>

        <form @submit.prevent="createDoctor" class="space-y-4">
          <div class="space-y-1">
            <label class="block text-xs font-semibold text-slate-700">Doctor Full Name</label>
            <input
              v-model="doctorForm.name"
              type="text"
              required
              placeholder="e.g. Dr. Kasun Bandara"
              class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:bg-white"
            />
          </div>

          <div class="space-y-1">
            <label class="block text-xs font-semibold text-slate-700">Email Address (Login)</label>
            <input
              v-model="doctorForm.email"
              type="email"
              required
              placeholder="e.g. dr.kasun@clinic.lk"
              class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:bg-white"
            />
            <div v-if="doctorForm.errors.email" class="text-xs text-rose-500">{{ doctorForm.errors.email }}</div>
          </div>

          <div class="space-y-1">
            <label class="block text-xs font-semibold text-slate-700">Initial Password</label>
            <input
              v-model="doctorForm.password"
              type="text"
              required
              placeholder="Password"
              class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:bg-white"
            />
          </div>

          <div class="grid grid-cols-2 gap-3">
            <div class="space-y-1">
              <label class="block text-xs font-semibold text-slate-700">Assigned Room</label>
              <input
                v-model="doctorForm.room_number"
                type="text"
                placeholder="e.g. Room 02"
                class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:bg-white"
              />
            </div>

            <div class="space-y-1">
              <label class="block text-xs font-semibold text-slate-700">Phone</label>
              <input
                v-model="doctorForm.phone"
                type="tel"
                placeholder="077xxxxxxx"
                class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:bg-white"
              />
            </div>
          </div>

          <div class="space-y-1">
            <label class="block text-xs font-semibold text-slate-700">Specialization</label>
            <input
              v-model="doctorForm.specialization"
              type="text"
              placeholder="e.g. Consultant Pediatrician"
              class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:bg-white"
            />
          </div>

          <div class="pt-2 flex gap-3">
            <button
              type="submit"
              :disabled="doctorForm.processing"
              class="flex-1 py-2.5 px-4 rounded-xl bg-teal-600 hover:bg-teal-700 text-white font-bold text-xs transition shadow-sm"
            >
              Register Doctor
            </button>
            <button
              type="button"
              @click="showDoctorModal = false"
              class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold transition"
            >
              Cancel
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- MODAL 3: REGISTER NEW CASHIER -->
    <div
      v-if="showCashierModal"
      class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-50 flex items-center justify-center p-4"
    >
      <div class="bg-white rounded-3xl p-6 sm:p-8 max-w-md w-full shadow-2xl relative space-y-5 border border-slate-200">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
          <div class="flex items-center gap-2 text-amber-800 font-bold text-sm">
            <Receipt class="w-5 h-5 text-amber-600" />
            <span>Register Reception / Cashier Staff</span>
          </div>
          <button @click="showCashierModal = false" class="p-1 rounded-full text-slate-400 hover:text-slate-600">
            <X class="w-5 h-5" />
          </button>
        </div>

        <form @submit.prevent="createCashier" class="space-y-4">
          <div class="space-y-1">
            <label class="block text-xs font-semibold text-slate-700">Staff Full Name</label>
            <input
              v-model="cashierForm.name"
              type="text"
              required
              placeholder="e.g. Nimali Senanayake"
              class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:bg-white"
            />
          </div>

          <div class="space-y-1">
            <label class="block text-xs font-semibold text-slate-700">Email Address (Login)</label>
            <input
              v-model="cashierForm.email"
              type="email"
              required
              placeholder="e.g. receptionist@clinic.lk"
              class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:bg-white"
            />
            <div v-if="cashierForm.errors.email" class="text-xs text-rose-500">{{ cashierForm.errors.email }}</div>
          </div>

          <div class="space-y-1">
            <label class="block text-xs font-semibold text-slate-700">Initial Password</label>
            <input
              v-model="cashierForm.password"
              type="text"
              required
              placeholder="Password"
              class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:bg-white"
            />
          </div>

          <div class="grid grid-cols-2 gap-3">
            <div class="space-y-1">
              <label class="block text-xs font-semibold text-slate-700">Assigned Counter</label>
              <input
                v-model="cashierForm.room_number"
                type="text"
                placeholder="e.g. Counter 02"
                class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:bg-white"
              />
            </div>

            <div class="space-y-1">
              <label class="block text-xs font-semibold text-slate-700">Phone</label>
              <input
                v-model="cashierForm.phone"
                type="tel"
                placeholder="071xxxxxxx"
                class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:bg-white"
              />
            </div>
          </div>

          <div class="pt-2 flex gap-3">
            <button
              type="submit"
              :disabled="cashierForm.processing"
              class="flex-1 py-2.5 px-4 rounded-xl bg-teal-600 hover:bg-teal-700 text-white font-bold text-xs transition shadow-sm"
            >
              Register Staff
            </button>
            <button
              type="button"
              @click="showCashierModal = false"
              class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold transition"
            >
              Cancel
            </button>
          </div>
        </form>
      </div>
    </div>

  </div>
</template>
