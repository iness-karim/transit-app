<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed, ref, onMounted } from 'vue';
import { useTrans } from '@/Composables/useTrans';

const { trans, currentLocale } = useTrans();
const props = defineProps({
    stats: Object,
    recent_operations: Array,
    transports: Array,
});

const briefing = ref(null);
const isBriefingLoading = ref(true);
const maintenancePredictions = ref({});
const isMaintenanceLoading = ref({});

const fetchBriefing = async () => {
    try {
        const res = await fetch(route('dashboard.briefing'));
        briefing.value = await res.json();
    } catch (e) {
        console.error('Briefing failed:', e);
    } finally {
        isBriefingLoading.value = false;
    }
};

const fetchMaintenance = async (transportId) => {
    isMaintenanceLoading.value[transportId] = true;
    try {
        const res = await fetch(route('transports.predict', transportId));
        maintenancePredictions.value[transportId] = await res.json();
    } catch (e) {
        console.error('Maintenance prediction failed:', e);
    } finally {
        isMaintenanceLoading.value[transportId] = false;
    }
};

const formatDate = (dateString) => {
    if (!dateString) return 'N/A';
    return new Date(dateString).toLocaleDateString(currentLocale.value === 'en' ? 'en-US' : 'fr-FR', {
        month: 'short',
        day: 'numeric',
        year: 'numeric'
    });
};

onMounted(() => {
    fetchBriefing();
});

const statsList = computed(() => [
    { name: trans('dash_total_transports'), value: props.stats.transports, icon: 'M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0', color: 'bg-indigo-500' },
    { name: trans('dash_active_drivers'), value: props.stats.drivers, icon: 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z', color: 'bg-emerald-500' },
    { name: trans('dash_ongoing_trajets'), value: props.stats.routes, icon: 'M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-1.447-.894L15 7m0 10V7', color: 'bg-amber-500' },
    { name: trans('dash_fleet_condition'), value: props.stats.fuel_avg, icon: 'M13 10V3L4 14h7v7l9-11h-7z', color: 'bg-rose-500' },
]);

import { Line } from 'vue-chartjs';
import {
    Chart as ChartJS,
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    Title,
    Tooltip,
    Legend,
    Filler
} from 'chart.js';

ChartJS.register(
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    Title,
    Tooltip,
    Legend,
    Filler
);

const chartData = computed(() => ({
    labels: props.weekly_missions?.map(m => m.date) || [],
    datasets: [
        {
            label: 'Missions',
            backgroundColor: 'rgba(79, 70, 229, 0.1)',
            borderColor: '#4f46e5',
            data: props.weekly_missions?.map(m => m.count) || [],
            tension: 0.4,
            fill: true,
            pointBackgroundColor: '#4f46e5',
            pointBorderColor: '#fff',
            pointBorderWidth: 2,
            pointRadius: 4,
        }
    ]
}));

const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: { display: false },
        tooltip: {
            backgroundColor: '#1e293b',
            titleFont: { size: 14, weight: 'bold' },
            padding: 12,
            cornerRadius: 12,
        }
    },
    scales: {
        y: { 
            beginAtZero: true,
            grid: { color: 'rgba(0,0,0,0.05)' },
            ticks: { precision: 0 }
        },
        x: { 
            grid: { display: false }
        }
    }
};
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            {{ trans('dash_title') }}
        </template>

        <div class="space-y-8">
            <!-- Hero Section & AI Briefing -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <div class="lg:col-span-2 relative overflow-hidden rounded-[2.5rem] bg-indigo-600 p-10 shadow-2xl shadow-indigo-500/20">
                    <div class="relative z-10 text-white">
                        <h2 class="text-4xl font-black mb-3">{{ currentLocale === 'en' ? 'Welcome back' : 'Bon retour' }}, {{ $page.props.auth.user.name }}!</h2>
                        <p class="text-indigo-100 text-xl font-medium max-w-xl">{{ currentLocale === 'en' ? 'Your transit network is performing at peak efficiency today. Check out the latest updates below.' : 'Votre réseau de transport fonctionne avec une efficacité maximale aujourd\'hui. Consultez les dernières mises à jour ci-dessous.' }}</p>
                    </div>
                    
                    <!-- Abstract Background Shapes -->
                    <div class="absolute -top-24 -right-24 h-64 w-64 rounded-full bg-indigo-500 blur-3xl opacity-50"></div>
                    <div class="absolute -bottom-24 -left-24 h-64 w-64 rounded-full bg-purple-500 blur-3xl opacity-30"></div>
                </div>

                <!-- AI Pulse Card -->
                <div class="rounded-[2.5rem] bg-gradient-to-br from-slate-900 to-indigo-950 p-8 text-white shadow-2xl relative overflow-hidden flex flex-col justify-between">
                    <div v-if="isBriefingLoading" class="animate-pulse space-y-4">
                        <div class="h-4 w-24 bg-white/20 rounded-full"></div>
                        <div class="h-8 w-full bg-white/20 rounded-xl"></div>
                        <div class="h-20 w-full bg-white/20 rounded-2xl"></div>
                    </div>
                    <div v-else-if="briefing" class="relative z-10">
                        <div class="flex items-center justify-between mb-6">
                            <span class="px-3 py-1 bg-indigo-500/30 rounded-full text-[10px] font-black uppercase tracking-widest text-indigo-300 border border-indigo-400/30">AI Fleet Pulse</span>
                            <span class="text-2xl font-black text-indigo-400">{{ briefing.pulse_score }}%</span>
                        </div>
                        <h3 class="text-xl font-black leading-tight mb-4">{{ briefing.headline }}</h3>
                        <p class="text-indigo-200/70 text-sm italic mb-6">"{{ briefing.strategic_advice }}"</p>
                        <div class="flex flex-wrap gap-2">
                            <span v-for="alert in briefing.critical_alerts" :key="alert" class="px-2 py-1 bg-rose-500/20 border border-rose-500/30 text-rose-300 text-[10px] font-bold rounded-lg leading-tight max-w-[200px]">
                                {{ typeof alert === 'object' ? Object.values(alert).join(': ') : alert }}
                            </span>
                        </div>
                    </div>
                    <div v-else class="text-slate-500 text-xs italic">AI Briefing service unavailable</div>
                    
                    <div class="absolute top-0 right-0 p-4 opacity-10">
                        <svg class="w-24 h-24" fill="currentColor" viewBox="0 0 24 24"><path d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    </div>
                </div>
            </div>

            <!-- Stats Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <div v-for="stat in statsList" :key="stat.name" class="bg-white dark:bg-slate-900 p-6 rounded-3xl shadow-sm border border-slate-100 dark:border-slate-800 hover:shadow-md transition-shadow">
                    <div class="flex items-center justify-between mb-4">
                        <div :class="stat.color" class="p-3 rounded-2xl text-white shadow-lg shadow-current/20">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="stat.icon" />
                            </svg>
                        </div>
                    </div>
                    <p class="text-slate-500 dark:text-slate-400 text-sm font-medium">{{ stat.name }}</p>
                    <h3 class="text-2xl font-bold mt-1">{{ stat.value }}</h3>
                </div>
            </div>

            <!-- Fleet Activity Chart -->
            <div class="bg-white dark:bg-slate-900 p-8 rounded-[2.5rem] border border-slate-100 dark:border-slate-800 shadow-sm">
                <div class="flex items-center justify-between mb-8">
                    <div>
                        <h3 class="text-xl font-bold text-slate-800 dark:text-white">Fleet Performance</h3>
                        <p class="text-xs text-slate-400 font-medium">Mission volume over the last 7 days</p>
                    </div>
                </div>
                <div class="h-[250px] relative">
                    <Line :data="chartData" :options="chartOptions" />
                </div>
            </div>

            <!-- Recent Activity Ticker -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <div class="lg:col-span-2 bg-white dark:bg-slate-900 p-8 rounded-3xl border border-slate-100 dark:border-slate-800 shadow-sm transition-all text-slate-800 dark:text-slate-200">
                    <div class="flex items-center justify-between mb-8">
                        <h3 class="text-xl font-bold">{{ trans('dash_realtime_ops') }}</h3>
                        <Link :href="route('routes.index')" class="text-indigo-600 font-semibold text-sm hover:underline">{{ currentLocale === 'en' ? 'View All' : 'Tout Voir' }}</Link>
                    </div>
                    
                    <div class="space-y-6">
                    <div v-for="op in recent_operations" :key="op.id" class="flex items-center group">
                        <div class="w-12 h-12 rounded-2xl bg-indigo-50 dark:bg-indigo-900/30 flex items-center justify-center text-indigo-600 mr-4 group-hover:bg-indigo-600 group-hover:text-white transition-all">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0" stroke-width="2" /></svg>
                        </div>
                        <div class="flex-1">
                            <div class="flex justify-between items-start">
                                <h4 class="font-bold">Mission #{{ op.id }} - {{ op.transport?.name }}</h4>
                                <span class="text-xs text-slate-400 font-medium">{{ op.time || 'Today' }}</span>
                            </div>
                            <p class="text-slate-500 text-xs">{{ op.start_location }} → {{ op.end_location }}</p>
                            <div class="mt-1 flex items-center">
                                <span class="w-2 h-2 rounded-full mr-2" :class="op.status === 'completed' ? 'bg-emerald-500' : 'bg-amber-500'"></span>
                                <span class="text-[10px] font-bold uppercase tracking-widest text-slate-400">{{ op.status.replace('_', ' ') }}</span>
                            </div>
                        </div>
                    </div>

                    <div v-if="recent_operations.length === 0" class="text-center py-10 text-slate-400 italic">
                        No recent operations found.
                    </div>
                </div>
                </div>

                <div class="space-y-8">
                    <div class="bg-gradient-to-br from-indigo-900 to-slate-900 p-8 rounded-[2.5rem] shadow-xl text-white relative overflow-hidden">
                        <div class="relative z-10">
                            <h3 class="text-xl font-bold mb-6 flex items-center">
                                <span class="p-2 bg-white/10 rounded-xl mr-3">🚀</span>
                                {{ trans('dash_quick_actions') }}
                            </h3>
                            <div class="space-y-4">
                                <Link :href="route('transports.index')" class="w-full py-4 px-6 bg-white/10 hover:bg-white/20 backdrop-blur-md rounded-2xl text-left font-bold transition-all group flex items-center justify-between border border-white/5">
                                    {{ trans('dash_add_transport') }}
                                    <svg class="w-5 h-5 opacity-0 group-hover:opacity-100 -translate-x-2 group-hover:translate-x-0 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                    </svg>
                                </Link>
                                <Link :href="route('drivers.index')" class="w-full py-4 px-6 bg-white/10 hover:bg-white/20 backdrop-blur-md rounded-2xl text-left font-bold transition-all group flex items-center justify-between border border-white/5">
                                    {{ trans('dash_assign_driver') }}
                                    <svg class="w-5 h-5 opacity-0 group-hover:opacity-100 -translate-x-2 group-hover:translate-x-0 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                    </svg>
                                </Link>
                                <Link :href="route('routes.index')" class="w-full py-4 px-6 bg-white/10 hover:bg-white/20 backdrop-blur-md rounded-2xl text-left font-bold transition-all group flex items-center justify-between border border-white/5">
                                    {{ trans('dash_new_trajet') }}
                                    <svg class="w-5 h-5 opacity-0 group-hover:opacity-100 -translate-x-2 group-hover:translate-x-0 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                    </svg>
                                </Link>
                            </div>
                        </div>
                    </div>

                    <!-- Predictive Maintenance Card -->
                    <div class="bg-white dark:bg-slate-900 p-8 rounded-[2.5rem] border border-slate-100 dark:border-slate-800 shadow-sm">
                        <h3 class="text-xl font-black mb-6 flex items-center text-slate-800 dark:text-white">
                            <span class="p-2 bg-rose-50 dark:bg-rose-900/30 rounded-xl mr-3">🛠️</span>
                            AI Smart Maintenance
                        </h3>
                        <div class="space-y-4">
                            <div v-for="transport in transports.slice(0, 3)" :key="transport.id" class="p-5 rounded-2xl border border-slate-50 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/30">
                                <div class="flex justify-between items-start mb-2">
                                    <div>
                                        <p class="font-black text-slate-800 dark:text-slate-200">{{ transport.brand }}</p>
                                        <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest">{{ transport.plate_number }}</p>
                                    </div>
                                    <button 
                                        @click="fetchMaintenance(transport.id)"
                                        class="text-[10px] font-black uppercase tracking-tighter text-indigo-600 hover:text-indigo-700 disabled:opacity-50"
                                        :disabled="isMaintenanceLoading[transport.id]"
                                    >
                                        {{ isMaintenanceLoading[transport.id] ? 'Analyzing...' : 'Predict' }}
                                    </button>
                                </div>
                                
                                <div v-if="maintenancePredictions[transport.id]" class="mt-4 pt-4 border-t border-slate-100 dark:border-slate-800 animate-in fade-in slide-in-from-top-2 duration-500">
                                    <div class="flex items-center justify-between mb-1">
                                        <span class="text-[10px] font-black text-slate-400 uppercase">Estimated Date</span>
                                        <span class="text-xs font-bold text-rose-500">
                                            {{ maintenancePredictions[transport.id].next_service_estimate?.includes('T') ? formatDate(maintenancePredictions[transport.id].next_service_estimate) : maintenancePredictions[transport.id].next_service_estimate }}
                                        </span>
                                    </div>
                                    <p class="text-[10px] text-slate-500 italic leading-tight">{{ maintenancePredictions[transport.id].reasoning }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
