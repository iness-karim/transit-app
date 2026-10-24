<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { useTrans } from '@/Composables/useTrans';

const { trans } = useTrans();

const props = defineProps({
    mission: Object,
    driver: Object
});

const updateTimestamp = (routeId, field, status = null) => {
    const time = new Date().toISOString().slice(0, 19).replace('T', ' ');
    useForm({
        field: field,
        time: time,
        status: status
    }).post(route('driver.update-time', routeId));
};

const formatTime = (time) => {
    if (!time) return trans('placeholder_pending');
    return new Date(time).toLocaleString([], { dateStyle: 'short', timeStyle: 'short' });
};
</script>

<template>
    <Head :title="trans('nav_missions')" />

    <AuthenticatedLayout>
        <template #header>
            {{ trans('nav_missions') }}: {{ driver.first_name }} {{ driver.last_name }}
        </template>

        <div class="space-y-10 pb-20">
            <!-- Active Mission Controller -->
            <div v-if="mission" class="bg-white dark:bg-slate-900 rounded-[2.5rem] border border-slate-100 dark:border-slate-800 shadow-xl overflow-hidden">
                <!-- Header Info -->
                <div class="p-10 bg-slate-900 text-white relative flex flex-col md:flex-row md:items-center justify-between gap-6">
                    <div class="absolute top-0 right-0 p-10 opacity-10 pointer-events-none">
                        <svg class="w-40 h-40" fill="currentColor" viewBox="0 0 24 24"><path d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0" /></svg>
                    </div>
                    
                    <div class="relative z-10">
                        <div class="flex items-center space-x-3 mb-2">
                            <span class="bg-indigo-500 text-white text-[10px] font-black px-2 py-0.5 rounded uppercase tracking-widest">{{ trans('status_ongoing') }}</span>
                            <span class="text-indigo-300 font-mono text-lg">#{{ mission.id.toString().padStart(5, '0') }}</span>
                        </div>
                        <h2 class="text-4xl font-black tracking-tighter uppercase whitespace-nowrap">{{ mission.origin }} <span class="text-indigo-400">→</span> {{ mission.destination }}</h2>
                    </div>

                    <div class="relative z-10 bg-white/5 backdrop-blur-md p-6 rounded-3xl border border-white/10 shrink-0">
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">{{ trans('trajets_label_transport') }}</p>
                        <div class="flex items-center">
                            <div class="w-10 h-10 bg-indigo-500/20 rounded-xl flex items-center justify-center mr-4 text-indigo-400">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0" stroke-width="2" /></svg>
                            </div>
                            <div>
                                <p class="font-bold text-lg leading-tight">{{ mission.transport?.name }}</p>
                                <p class="text-xs text-indigo-300/60 font-mono">{{ mission.transport?.plate_number }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Status Tracking Timeline -->
                <div class="p-10 grid grid-cols-1 md:grid-cols-3 gap-8">
                    <!-- Step 1: Departure -->
                    <div class="bg-slate-50 dark:bg-slate-800/50 p-8 rounded-[2rem] border border-slate-100 dark:border-slate-800 flex flex-col group relative overflow-hidden">
                        <div v-if="mission.departure_date" class="absolute top-0 right-0 p-4 text-emerald-500">
                            <svg class="w-8 h-8 opacity-20" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" /></svg>
                        </div>
                        <label class="text-xs font-black text-slate-400 uppercase tracking-[0.2em] mb-4">Phase 01: {{ trans('status_departure') }}</label>
                        <div class="text-sm font-bold text-slate-600 dark:text-slate-300 mb-6">Record your start from company base.</div>
                        <div class="mt-auto">
                            <p class="text-[10px] font-black text-indigo-500 uppercase tracking-widest mb-1">Time Recorded</p>
                            <p class="text-lg font-black text-slate-800 dark:text-white">{{ formatTime(mission.departure_date) }}</p>
                            <button 
                                v-if="!mission.departure_date" 
                                @click="updateTimestamp(mission.id, 'departure_date')"
                                class="w-full mt-4 py-3 bg-indigo-600 text-white rounded-xl font-black text-[10px] uppercase tracking-widest hover:bg-indigo-700 transition-all shadow-lg shadow-indigo-500/20"
                            >
                                Mark Departure
                            </button>
                        </div>
                    </div>

                    <!-- Step 2: Delivery -->
                    <div class="bg-slate-50 dark:bg-slate-800/50 p-8 rounded-[2rem] border border-slate-100 dark:border-slate-800 flex flex-col group relative overflow-hidden" :class="!mission.departure_date ? 'opacity-50 grayscale' : ''">
                        <label class="text-xs font-black text-slate-400 uppercase tracking-[0.2em] mb-4">Phase 02: {{ trans('status_delivered') }}</label>
                        <div class="text-sm font-bold text-slate-600 dark:text-slate-300 mb-6">Record time of successful delivery.</div>
                        <div class="mt-auto">
                            <p class="text-[10px] font-black text-indigo-500 uppercase tracking-widest mb-1">Time Recorded</p>
                            <p class="text-lg font-black text-slate-800 dark:text-white">{{ formatTime(mission.delivery_date) }}</p>
                            
                            <div v-if="mission.departure_date && !mission.delivery_date" class="flex flex-col space-y-2 mt-4">
                                <button 
                                    @click="updateTimestamp(mission.id, 'delivery_date', 'completed')"
                                    class="w-full py-3 bg-emerald-600 text-white rounded-xl font-black text-[10px] uppercase tracking-widest hover:bg-emerald-700 transition-all shadow-lg shadow-emerald-500/20"
                                >
                                    Mark Delivered
                                </button>
                                <button 
                                    @click="updateTimestamp(mission.id, 'delivery_date', 'failed')"
                                    class="w-full py-2 bg-rose-500/10 text-rose-500 rounded-xl font-black text-[10px] uppercase tracking-widest hover:bg-rose-500 hover:text-white transition-all border border-rose-500/20"
                                >
                                    Mark as Failed
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Step 3: Return -->
                    <div class="bg-slate-50 dark:bg-slate-800/50 p-8 rounded-[2rem] border border-slate-100 dark:border-slate-800 flex flex-col group relative overflow-hidden" :class="!mission.delivery_date ? 'opacity-50 grayscale' : ''">
                        <label class="text-xs font-black text-slate-400 uppercase tracking-[0.2em] mb-4">Phase 03: {{ trans('status_return') }}</label>
                        <div class="text-sm font-bold text-slate-600 dark:text-slate-300 mb-6">Arrival back at company headquarters.</div>
                        <div class="mt-auto">
                            <p class="text-[10px] font-black text-indigo-500 uppercase tracking-widest mb-1">Time Recorded</p>
                            <p class="text-lg font-black text-slate-800 dark:text-white">{{ formatTime(mission.return_date) }}</p>
                            <button 
                                v-if="mission.delivery_date && !mission.return_date" 
                                @click="updateTimestamp(mission.id, 'return_date')"
                                class="w-full mt-4 py-3 bg-slate-900 text-white rounded-xl font-black text-[10px] uppercase tracking-widest hover:bg-slate-800 transition-all shadow-lg"
                            >
                                Mark Returned
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div v-else class="py-20 text-center bg-white dark:bg-slate-900 rounded-[3rem] border border-dashed border-slate-200 dark:border-slate-800">
                <div class="w-20 h-20 bg-slate-50 dark:bg-slate-800 rounded-full flex items-center justify-center mx-auto mb-6 text-slate-300">
                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                </div>
                <h3 class="text-2xl font-black uppercase tracking-tight text-slate-400">No Active Mission</h3>
                <p class="text-slate-500 mt-2">Wait for the administrator to deploy a mission for you.</p>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
