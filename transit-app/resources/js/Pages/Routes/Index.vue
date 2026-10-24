<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref, nextTick } from 'vue';
import html2pdf from 'html2pdf.js';
import DeliveryNoteTemplate from '@/Components/DeliveryNoteTemplate.vue';
import LiveMap from '@/Components/LiveMap.vue';
import { useTrans } from '@/Composables/useTrans';

const { trans } = useTrans();

const props = defineProps({
    routes: Array,
    transports: Array,
    drivers: Array,
});

const isModalOpen = ref(false);

const form = useForm({
    transport_id: '',
    driver_id: '',
    start_location: '',
    end_location: '',
    status: 'pending',
    cargo_type: '',
    estimated_arrival: '',
    total_distance: '',
    weather: 'Clear',
    traffic_status: 'Fluid',
    notes: '',
});

const openModal = () => {
    form.reset();
    isModalOpen.value = true;
};

const submit = () => {
    form.post(route('routes.store'), {
        onSuccess: () => closeModal(),
    });
};

const closeModal = () => {
    isModalOpen.value = false;
    form.reset();
};

const pdfRoute = ref(null);

const downloadPdf = async (routeObj) => {
    if (pdfRoute.value) return;
    
    pdfRoute.value = routeObj;
    await nextTick();
    
    const element = document.getElementById('delivery-note-container');
    const opt = {
      margin:       0,
      filename:     `BL-RAGHD-${String(routeObj.id).padStart(5, '0')}.pdf`,
      image:        { type: 'jpeg', quality: 0.98 },
      html2canvas:  { scale: 2, useCORS: true },
      jsPDF:        { unit: 'in', format: 'a4', orientation: 'portrait' }
    };
    
    html2pdf().set(opt).from(element).save().then(() => {
        pdfRoute.value = null;
    });
};

const updateStatus = (routeObj, newStatus) => {
    useForm({
        status: newStatus,
        current_lat: routeObj.current_lat,
        current_lng: routeObj.current_lng,
        cargo_type: routeObj.cargo_type,
        estimated_arrival: routeObj.estimated_arrival,
        total_distance: routeObj.total_distance,
        weather: routeObj.weather,
        traffic_status: routeObj.traffic_status,
        notes: routeObj.notes,
    }).put(route('routes.update', routeObj.id));
};

const isPredicting = ref(null); // ID of the route being predicted
const predictionResult = ref(null);
const isAiModalOpen = ref(false);

const runAiPrediction = async (routeObj) => {
    isPredicting.value = routeObj.id;
    try {
        const response = await fetch(route('routes.predict', routeObj.id));
        predictionResult.value = await response.json();
        isAiModalOpen.value = true;
    } catch (error) {
        console.error('AI Prediction failed:', error);
    } finally {
        isPredicting.value = null;
    }
};

const getStatusClass = (status) => {
    switch (status) {
        case 'completed': return 'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400';
        case 'in_progress': return 'bg-blue-50 text-blue-600 dark:bg-blue-500/10 dark:text-blue-400';
        case 'cancelled': return 'bg-rose-50 text-rose-600 dark:bg-rose-500/10 dark:text-rose-400';
        default: return 'bg-slate-50 text-slate-600 dark:bg-slate-500/10 dark:text-slate-400';
    }
};
</script>

<template>
    <Head :title="trans('nav_routes')" />

    <AuthenticatedLayout>
        <template #header>
            {{ trans('routes_title') }}
        </template>

        <div class="space-y-6">
            <!-- Top Actions -->
            <div class="flex justify-between items-center">
                <div>
                    <h2 class="text-2xl font-bold">{{ trans('routes_active_missions') }}</h2>
                    <p class="text-slate-500 text-sm">{{ trans('routes_subtitle') }}</p>
                </div>
                <button 
                    @click="openModal()"
                    class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-3 rounded-2xl font-bold shadow-lg shadow-indigo-500/30 transition-all flex items-center"
                >
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    {{ trans('routes_new') }}
                </button>
            </div>
            <!-- Route Map Visualization -->
            <div class="mb-8">
                <LiveMap :routes="routes" />
            </div>

            <!-- Dashboard List for Routes (Full Width / Wide) -->
            <div class="space-y-6">
                <!-- Route Tracking Cards -->
                <!-- Redesigned "Thin" Route Tracking Cards -->
                <div v-for="route in routes" :key="route.id" class="bg-white dark:bg-slate-900 rounded-[2rem] border border-slate-100 dark:border-slate-800 shadow-sm overflow-hidden hover:shadow-xl hover:border-indigo-100 transition-all duration-300 group/card">
                    <div class="p-6 flex flex-col lg:flex-row lg:items-center gap-6">
                        <!-- Ident & Status -->
                        <div class="flex items-center space-x-4 border-b lg:border-b-0 lg:border-r border-slate-50 dark:border-slate-800 pb-4 lg:pb-0 lg:pr-6 shrink-0 min-w-[180px]">
                            <div class="w-12 h-12 rounded-2xl bg-indigo-50 dark:bg-indigo-900/30 flex items-center justify-center text-indigo-600 shrink-0">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                            </div>
                            <div>
                                <h4 class="font-black text-slate-900 dark:text-white leading-tight">#{{ route.id.toString().padStart(5, '0') }}</h4>
                                <div :class="getStatusClass(route.status)" class="text-[9px] font-black uppercase tracking-widest px-2 py-0.5 rounded-md mt-1 w-fit">
                                    {{ route.status.replace('_', ' ') }}
                                </div>
                            </div>
                        </div>

                        <!-- Path & Cargo -->
                        <div class="flex-grow space-y-3">
                            <div class="flex items-center space-x-3">
                                <span class="text-xs font-black text-indigo-600 bg-indigo-50 dark:bg-indigo-900/30 px-2 py-1 rounded-lg">{{ route.start_location }}</span>
                                <div class="flex-grow h-px bg-gradient-to-r from-indigo-200 via-slate-100 to-rose-200 relative">
                                    <div class="absolute inset-0 flex items-center justify-center">
                                        <svg class="w-4 h-4 text-slate-300 bg-white dark:bg-slate-900 rounded-full" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                                    </div>
                                </div>
                                <span class="text-xs font-black text-rose-500 bg-rose-50 dark:bg-rose-900/30 px-2 py-1 rounded-lg">{{ route.end_location }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-4">
                                    <div class="flex items-center text-[11px] font-bold text-slate-500">
                                        <svg class="w-4 h-4 mr-1 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" stroke-width="2" /></svg>
                                        {{ route.cargo_type || 'General Cargo' }}
                                    </div>
                                    <div class="flex items-center text-[11px] font-bold text-slate-500">
                                        <svg class="w-4 h-4 mr-1 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" stroke-width="2" /></svg>
                                        {{ route.total_distance }} KM
                                    </div>
                                </div>
                                <div class="text-[10px] font-bold text-slate-400 bg-slate-50 dark:bg-slate-800/50 px-2 py-1 rounded-lg">
                                    ETA: {{ route.estimated_arrival ? new Date(route.estimated_arrival).toLocaleString() : 'Not Set' }}
                                </div>
                            </div>
                        </div>

                        <!-- Environmental & Assets -->
                        <div class="flex items-center space-x-6 lg:border-l border-slate-50 dark:border-slate-800 lg:pl-6">
                            <!-- Weather & Traffic -->
                            <div class="shrink-0 space-y-1">
                                <div class="flex items-center text-[10px] font-black uppercase" :class="route.weather === 'Clear' ? 'text-sky-500' : 'text-amber-500'">
                                    <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" stroke-width="2" /></svg>
                                    {{ route.weather }}
                                </div>
                                <div class="flex items-center text-[10px] font-black uppercase" :class="route.traffic_status === 'Fluid' ? 'text-emerald-500' : 'text-rose-500'">
                                    <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6" stroke-width="2" /></svg>
                                    {{ route.traffic_status }}
                                </div>
                            </div>

                            <!-- Vehicle & Driver -->
                            <div class="shrink-0 space-y-1 min-w-[120px]">
                                <div class="flex items-center text-[11px] font-bold text-slate-700 dark:text-slate-300">
                                    <div class="w-5 h-5 bg-indigo-50 dark:bg-indigo-900/30 flex items-center justify-center rounded-md mr-2">
                                        <svg class="w-3 h-3 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0" stroke-width="2" /></svg>
                                    </div>
                                    <span class="truncate">{{ route.transport?.plate_number }}</span>
                                </div>
                                <div class="flex items-center text-[11px] font-bold text-slate-700 dark:text-slate-300">
                                    <div class="w-5 h-5 bg-sky-50 dark:bg-sky-900/30 flex items-center justify-center rounded-md mr-2">
                                        <svg class="w-3 h-3 text-sky-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" stroke-width="2" /></svg>
                                    </div>
                                    <span class="truncate">{{ route.driver?.first_name }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Actions Area -->
                        <div class="flex items-center space-x-2 shrink-0 border-t lg:border-t-0 lg:border-l border-slate-50 dark:border-slate-800 pt-4 lg:pt-0 lg:pl-6">
                            <button 
                                @click="runAiPrediction(route)"
                                :disabled="isPredicting === route.id"
                                class="w-10 h-10 rounded-xl bg-slate-900 text-white flex items-center justify-center hover:scale-105 transition-all shadow-lg disabled:opacity-50"
                                title="AI Insights"
                            >
                                <svg v-if="isPredicting === route.id" class="animate-spin w-5 h-5" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                <svg v-else class="w-5 h-5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                            </button>
                            
                            <div class="flex space-x-2">
                                <button v-if="route.status === 'pending'" @click="updateStatus(route, 'in_progress')" class="px-4 py-2.5 rounded-xl bg-indigo-600 text-white text-[10px] font-black hover:bg-indigo-700 transition-colors uppercase tracking-widest">{{ trans('routes_deploy') }}</button>
                                <button v-if="route.status === 'in_progress'" @click="updateStatus(route, 'completed')" class="px-4 py-2.5 rounded-xl bg-emerald-600 text-white text-[10px] font-black hover:bg-emerald-700 transition-colors uppercase tracking-widest">{{ trans('routes_arrive') }}</button>
                                <button v-if="route.status === 'completed'" @click="downloadPdf(route)" class="px-4 py-2.5 rounded-xl bg-sky-600 text-white text-[10px] font-black hover:bg-sky-700 transition-colors uppercase tracking-widest hover:scale-105" title="Télécharger le Bon de Livraison">
                                    <span class="flex items-center">
                                        <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                        BL PDF
                                    </span>
                                </button>
                                <button v-if="route.status !== 'completed' && route.status !== 'cancelled'" @click="updateStatus(route, 'cancelled')" class="p-2.5 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-500 hover:text-rose-600 transition-colors" title="Cancel">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-if="routes.length === 0" class="xl:col-span-2 py-20 bg-white dark:bg-slate-900 rounded-3xl border border-dashed border-slate-200 dark:border-slate-800 text-center text-slate-400">
                    <p class="text-lg font-medium">No active trajets found.</p>
                    <p class="text-sm">Initiate a new transport mission to start tracking.</p>
                </div>
            </div>
        </div>

        <!-- Modal -->
        <div v-if="isModalOpen" class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-slate-950/40 backdrop-blur-sm animate-in fade-in duration-200">
            <div class="bg-white dark:bg-slate-900 rounded-3xl w-full max-w-2xl shadow-2xl overflow-hidden animate-in zoom-in-95 duration-200 text-slate-800 dark:text-slate-200">
                <div class="p-8 border-b border-slate-100 dark:border-slate-800 flex justify-between items-center">
                    <h3 class="text-xl font-bold font-sans">{{ trans('routes_modal_title') }}</h3>
                    <button @click="closeModal" class="text-slate-400 hover:text-slate-600 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>

                <form @submit.prevent="submit" class="p-8 space-y-6">
                    <div class="grid grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label class="text-sm font-bold text-slate-500 uppercase tracking-wider">{{ trans('routes_label_transport') }}</label>
                            <select v-model="form.transport_id" class="w-full bg-slate-50 dark:bg-slate-800 border-0 rounded-2xl p-4 focus:ring-2 focus:ring-indigo-500 transition-all font-medium appearance-none">
                                <option value="" disabled>Select Vehicle</option>
                                <option v-for="t in transports" :key="t.id" :value="t.id">{{ t.name }} ({{ t.plate_number }})</option>
                            </select>
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-bold text-slate-500 uppercase tracking-wider">{{ trans('routes_label_driver') }}</label>
                            <select v-model="form.driver_id" class="w-full bg-slate-50 dark:bg-slate-800 border-0 rounded-2xl p-4 focus:ring-2 focus:ring-indigo-500 transition-all font-medium appearance-none">
                                <option value="" disabled>Assign Driver</option>
                                <option v-for="d in drivers" :key="d.id" :value="d.id">{{ d.first_name }} {{ d.last_name }}</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label class="text-sm font-bold text-slate-500 uppercase tracking-wider">{{ trans('routes_label_origin') }}</label>
                            <input v-model="form.start_location" type="text" placeholder="Start point" class="w-full bg-slate-50 dark:bg-slate-800 border-0 rounded-2xl p-4 focus:ring-2 focus:ring-indigo-500 transition-all font-medium">
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-bold text-slate-500 uppercase tracking-wider">{{ trans('routes_label_destination') }}</label>
                            <input v-model="form.end_location" type="text" placeholder="End point" class="w-full bg-slate-50 dark:bg-slate-800 border-0 rounded-2xl p-4 focus:ring-2 focus:ring-indigo-500 transition-all font-medium">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label class="text-sm font-bold text-slate-500 uppercase tracking-wider">{{ trans('routes_label_cargo') }}</label>
                            <input v-model="form.cargo_type" type="text" placeholder="e.g. Perishable Goods" class="w-full bg-slate-50 dark:bg-slate-800 border-0 rounded-2xl p-4 focus:ring-2 focus:ring-indigo-500 transition-all font-medium">
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-bold text-slate-500 uppercase tracking-wider">{{ trans('routes_label_distance') }}</label>
                            <input v-model="form.total_distance" type="number" class="w-full bg-slate-50 dark:bg-slate-800 border-0 rounded-2xl p-4 focus:ring-2 focus:ring-indigo-500 transition-all font-medium">
                        </div>
                    </div>

                    <div class="grid grid-cols-3 gap-6">
                        <div class="space-y-2">
                            <label class="text-sm font-bold text-slate-500 uppercase tracking-wider">{{ trans('routes_label_weather') }}</label>
                            <select v-model="form.weather" class="w-full bg-slate-50 dark:bg-slate-800 border-0 rounded-2xl p-4 focus:ring-2 focus:ring-indigo-500 transition-all font-medium appearance-none">
                                <option value="Clear">Clear</option>
                                <option value="Rainy">Rainy</option>
                                <option value="Stormy">Stormy</option>
                                <option value="Snowy">Snowy</option>
                            </select>
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-bold text-slate-500 uppercase tracking-wider">{{ trans('routes_label_traffic') }}</label>
                            <select v-model="form.traffic_status" class="w-full bg-slate-50 dark:bg-slate-800 border-0 rounded-2xl p-4 focus:ring-2 focus:ring-indigo-500 transition-all font-medium appearance-none">
                                <option value="Fluid">Fluid</option>
                                <option value="Moderate">Moderate</option>
                                <option value="Rain">Rain</option>
                                <option value="Fog">Fog</option>
                            </select>
                        </div>
                        <div class="space-y-3">
                            <label class="text-[11px] font-black text-slate-400 uppercase tracking-widest px-1">{{ trans('trajets_traffic') }}</label>
                            <select v-model="form.traffic_status" class="w-full bg-slate-50 dark:bg-slate-800 border-0 rounded-2xl p-5 focus:ring-4 focus:ring-indigo-500/20 transition-all font-bold appearance-none">
                                <option value="Normal">Normal</option>
                                <option value="Heavy">Heavy</option>
                                <option value="Critical">Critical</option>
                            </select>
                        </div>
                        <div class="space-y-3">
                            <label class="text-[11px] font-black text-slate-400 uppercase tracking-widest px-1">{{ trans('trajets_label_eta') }}</label>
                            <input v-model="form.estimated_arrival" type="datetime-local" class="w-full bg-slate-50 dark:bg-slate-800 border-0 rounded-2xl p-5 focus:ring-4 focus:ring-indigo-500/20 transition-all font-bold">
                        </div>
                    </div>

                    <div class="pt-8 flex space-x-6">
                        <button type="button" @click="isModalOpen = false" class="flex-1 py-5 px-8 rounded-2xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors font-black uppercase text-xs">{{ trans('btn_cancel') }}</button>
                        <button type="submit" :disabled="form.processing" class="flex-[2] py-5 px-8 rounded-2xl bg-indigo-600 hover:bg-indigo-700 text-white shadow-xl shadow-indigo-500/30 transition-all font-black uppercase text-xs tracking-widest disabled:opacity-50">
                            {{ trans('btn_deploy_mission') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- AI Analysis Modal (Overlay) -->
        <div v-if="isAiModalOpen" class="fixed inset-0 z-[100] flex items-center justify-center p-6 sm:p-12 overflow-hidden">
            <div class="absolute inset-0 bg-slate-900/90 backdrop-blur-2xl" @click="isAiModalOpen = false"></div>
            
            <div class="relative w-full max-w-6xl max-h-[90vh] bg-white dark:bg-slate-900 rounded-[3.5rem] shadow-[0_0_100px_rgba(79,70,229,0.2)] border border-white/10 flex flex-col overflow-hidden animate-in zoom-in-95 duration-500">
                <!-- Modal Header -->
                <div class="p-10 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between shrink-0 bg-slate-900 text-white">
                    <div class="flex items-center space-x-6">
                        <div class="w-16 h-16 bg-indigo-500 rounded-3xl flex items-center justify-center shadow-lg shadow-indigo-500/40">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                        </div>
                        <div>
                            <h3 class="text-3xl font-black uppercase tracking-tighter leading-none mb-1">AI Mission Analysis</h3>
                            <div class="flex items-center">
                                <span class="w-2 h-2 rounded-full bg-emerald-500 mr-2 animate-pulse"></span>
                                <p class="text-indigo-300 text-sm font-bold uppercase tracking-widest opacity-80">Deep Predictive Intelligence v2.0</p>
                            </div>
                        </div>
                    </div>
                    <button @click="isAiModalOpen = false" class="text-slate-500 hover:text-white transition-all bg-white/5 hover:bg-white/10 p-3 rounded-2xl">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>

                <div v-if="predictionResult" class="overflow-y-auto p-10 space-y-10 custom-scrollbar">
                    <!-- Error State -->
                    <div v-if="predictionResult.error" class="bg-rose-50 dark:bg-rose-900/20 border border-rose-100 dark:border-rose-900/30 p-8 rounded-[2rem] space-y-4">
                        <div class="flex items-center text-rose-600 dark:text-rose-400 text-xl font-black mb-2">
                            <svg class="w-8 h-8 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                            Critical Intelligence Error
                        </div>
                        <p class="text-rose-500 font-bold">{{ predictionResult.error }}</p>
                        <div class="text-xs text-slate-400 bg-white dark:bg-black/40 p-5 rounded-2xl border border-rose-100/50 dark:border-rose-900/30 font-mono">
                            {{ predictionResult.details }}
                        </div>
                        <button @click="isAiModalOpen = false" class="w-full py-4 bg-rose-600 text-white rounded-2xl font-black uppercase tracking-widest mt-4 hover:bg-rose-700 transition-all shadow-lg shadow-rose-500/20">Resume Troubleshooting</button>
                    </div>

                    <!-- Success State -->
                    <div v-else class="space-y-12">
                        <!-- Top KPI Row -->
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                            <div class="bg-slate-50 dark:bg-slate-800/50 p-6 rounded-[2rem] border border-slate-100 dark:border-slate-800 text-center">
                                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3">Health Score</p>
                                <div class="text-5xl font-black text-indigo-600">{{ predictionResult.safety_score || 0 }}%</div>
                            </div>
                            <div class="bg-slate-50 dark:bg-slate-800/50 p-6 rounded-[2rem] border border-slate-100 dark:border-slate-800 text-center">
                                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3">Breakdown Risk</p>
                                <div class="text-5xl font-black" :class="predictionResult.breakdown_risk_score > 5 ? 'text-rose-500' : 'text-emerald-500'">
                                    {{ predictionResult.breakdown_risk_score || 0 }}/10
                                </div>
                            </div>
                            <div class="bg-slate-50 dark:bg-slate-800/50 p-6 rounded-[2rem] border border-slate-100 dark:border-slate-800 text-center">
                                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3">Efficiency</p>
                                <div class="text-5xl font-black text-amber-500">{{ predictionResult.fuel_efficiency_est || 0 }}/10</div>
                            </div>
                            <div class="bg-slate-50 dark:bg-slate-800/50 p-6 rounded-[2rem] border border-slate-100 dark:border-slate-800 text-center relative">
                                <div v-if="predictionResult.ml_delay_probability !== undefined" class="absolute top-2 right-2 text-[8px] font-black uppercase bg-indigo-100 text-indigo-600 px-2 py-0.5 rounded shadow-sm">ML ENHANCED</div>
                                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3">Delay Risk</p>
                                <div class="text-4xl py-1 font-black uppercase tracking-tighter" :class="predictionResult.delay_probability === 'High' ? 'text-rose-500' : 'text-indigo-500'">
                                    {{ predictionResult.delay_probability || 'N/A' }}
                                </div>
                                <div v-if="predictionResult.ml_delay_probability !== undefined" class="mt-2 border-t border-slate-200 dark:border-slate-700 pt-2">
                                    <p class="text-xs font-bold text-slate-600 dark:text-slate-400">
                                        ML Probability: <span :class="predictionResult.ml_delay_predicted ? 'text-rose-500' : 'text-emerald-500'">{{ (predictionResult.ml_delay_probability * 100).toFixed(1) }}%</span>
                                    </p>
                                    <p class="text-[9px] font-black uppercase tracking-widest text-slate-400 mt-1">M: {{ predictionResult.ml_model_used }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 lg:grid-cols-3 gap-10">
                            <!-- Left Column: Detailed Observations -->
                            <div class="lg:col-span-2 space-y-8">
                                <section>
                                    <label class="text-xs font-black text-indigo-600 uppercase tracking-[0.2em] mb-4 block">Analytical Summary</label>
                                    <div class="bg-indigo-50/30 dark:bg-indigo-900/10 p-8 rounded-[2rem] border border-indigo-100 dark:border-indigo-900/30 italic text-lg leading-relaxed text-slate-700 dark:text-slate-300">
                                        "{{ predictionResult.observations }}"
                                    </div>
                                </section>

                                <section>
                                    <label class="text-xs font-black text-slate-400 uppercase tracking-[0.2em] mb-4 block">Tactical Recommendations</label>
                                    <div class="grid grid-cols-1 gap-4">
                                        <div v-for="(rec, idx) in predictionResult.recommendations" :key="idx" 
                                             class="flex items-center bg-white dark:bg-slate-800 p-5 rounded-2xl border border-slate-100 dark:border-slate-700 shadow-sm hover:translate-x-2 transition-transform">
                                            <div class="w-10 h-10 rounded-xl bg-indigo-50 dark:bg-indigo-900/30 flex items-center justify-center mr-4 text-indigo-600">
                                                <span class="font-bold">{{ idx + 1 }}</span>
                                            </div>
                                            <span class="font-semibold text-slate-600 dark:text-slate-400">{{ rec }}</span>
                                        </div>
                                    </div>
                                </section>
                            </div>

                            <!-- Right Column: Technical Matrix -->
                            <div class="space-y-8">
                                <section>
                                    <label class="text-xs font-black text-slate-400 uppercase tracking-[0.2em] mb-4 block">Environmental Factors</label>
                                    <div class="space-y-4">
                                        <div v-for="(val, key) in predictionResult.technical_details" :key="key" 
                                             class="bg-slate-50 dark:bg-slate-800 p-5 rounded-2xl border border-slate-100 dark:border-slate-800">
                                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">{{ key.replace('_', ' ') }}</p>
                                            <p class="text-sm font-bold text-slate-600 dark:text-slate-300 capitalize">
                                                {{ (val && typeof val === 'object') ? Object.values(val).join(', ') : (val || 'N/A') }}
                                            </p>
                                        </div>
                                    </div>
                                </section>

                                <section>
                                    <div class="p-8 rounded-[2rem] bg-gradient-to-br from-slate-900 to-indigo-950 text-white shadow-xl relative overflow-hidden">
                                        <div class="absolute top-0 right-0 p-4 opacity-20">
                                            <svg class="w-20 h-20" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2L1 21h22L12 2zm0 3.45L19.15 19H4.85L12 5.45zM11 16h2v2h-2v-2zm0-7h2v5h-2V9z"/></svg>
                                        </div>
                                        <p class="text-[10px] font-black uppercase tracking-widest text-indigo-300 mb-2">AI Verdict</p>
                                        <p class="text-2xl font-black mb-4">{{ predictionResult.etat_predit }}</p>
                                        <div class="w-full bg-white/10 rounded-full h-2 mb-2">
                                            <div class="bg-indigo-500 h-2 rounded-full transition-all duration-1000" :style="{ width: predictionResult.risk_percentage + '%' }"></div>
                                        </div>
                                        <p class="text-[10px] font-bold text-indigo-200 uppercase tracking-widest">Risk Index: {{ predictionResult.risk_percentage }}%</p>
                                    </div>
                                </section>
                            </div>
                        </div>

                        <button @click="isAiModalOpen = false" class="w-full py-6 bg-slate-900 hover:bg-slate-800 text-white rounded-[2rem] font-black uppercase tracking-[0.3em] transition-all shadow-2xl flex items-center justify-center group shrink-0">
                            Complete Briefing
                            <svg class="w-5 h-5 ml-3 group-hover:translate-x-2 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" /></svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Hidden PDF Container for html2pdf generation -->
        <div class="fixed left-[-9999px] top-0 pointer-events-none opacity-0">
            <DeliveryNoteTemplate v-if="pdfRoute" :route="pdfRoute" />
        </div>

    </AuthenticatedLayout>
</template>
