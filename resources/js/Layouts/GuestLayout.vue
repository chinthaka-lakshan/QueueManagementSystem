<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div
        class="flex min-h-screen flex-col items-center bg-slate-50 pt-8 sm:justify-center sm:pt-0 selection:bg-teal-500 selection:text-white"
    >
        <div class="flex flex-col items-center">
            <Link href="/" class="flex flex-col items-center gap-2 group transition">
                <ApplicationLogo class="h-14 w-14 shadow-lg shadow-teal-600/20 group-hover:scale-105 transition" />
                <span class="text-xl font-bold tracking-tight text-slate-900 mt-1 flex items-center gap-1.5">
                    CareQueue <span class="text-teal-700 text-xs px-2 py-0.5 rounded-full bg-teal-50 border border-teal-200 font-semibold">Healthcare</span>
                </span>
                <p class="text-xs text-slate-500">Hospital & Clinic Queue System</p>
            </Link>
        </div>

        <div
            class="mt-6 w-full overflow-hidden bg-white px-8 py-8 shadow-xl shadow-slate-200/50 sm:max-w-md sm:rounded-3xl border border-slate-200"
        >
            <slot />
        </div>
    </div>
</template>
