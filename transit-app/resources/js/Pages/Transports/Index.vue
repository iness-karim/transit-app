<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { useTrans } from '@/Composables/useTrans';

const { trans } = useTrans();

const props = defineProps({
    transports: Array,
});

const isModalOpen = ref(false);
const editingTransport = ref(null);

const form = useForm({
    name: '',
    plate_number: '',
    icon_predict: 'truck',
    status: 'active',
    brand: '',
    model_year: '',
    mileage: 0,
    last_oil_change: '',
    capacity: '',
    tire_condition: 'Good',
    fuel_level: 100,
});

const openModal = (transport = null) => {
    editingTransport.value = transport;
    if (transport) {
        form.name = transport.name || '';
        form.plate_number = transport.plate_number || '';
        form.icon_predict = transport.icon_predict || 'truck';
        form.status = transport.status || 'active';
        form.brand = transport.brand || '';
        form.model_year = transport.model_year || '';
        form.mileage = transport.mileage || 0;
        form.last_oil_change = transport.last_oil_change || '';
        form.capacity = transport.capacity || '';
        form.tire_condition = transport.tire_condition || 'Good';
        form.fuel_level = transport.fuel_level || 100;
    } else {
        form.reset();
    }
    isModalOpen.value = true;
};

const submit = () => {
    if (editingTransport.value) {
        form.put(route('transports.update', editingTransport.value.id), {
            onSuccess: () => closeModal(),
        });
    } else {
        form.post(route('transports.store'), {
            onSuccess: () => closeModal(),
        });
    }
};

const closeModal = () => {
    isModalOpen.value = false;
    editingTransport.value = null;
    form.reset();
};

const deleteTransport = (id) => {
    if (confirm(trans('confirm_delete'))) {
        useForm({}).delete(route('transports.destroy', id));
    }
};

const icons = ['truck', 'car', 'bus', 'van', 'ship', 'train', 'plane', 'drone', 'bike'];

const predictionResults = ref({});
const predictingIds = ref(new Set());

const predictMaintenance = async (id) => {
    predictingIds.value.add(id);
    try {
        const response = await fetch(route('transports.predict', id));
        const data = await response.json();
        predictionResults.value[id] = data;
    } catch (e) {
        console.error('Prediction failed', e);
    } finally {
        predictingIds.value.delete(id);
    }
};
</script>

<template>
    <Head :title="trans('nav_transports')" />

    <AuthenticatedLayout>
        <template #header>
            {{ trans('trans_title') }}
        </template>

        <div class="space-y-6">
            <!-- Top Actions -->
            <div class="flex justify-between items-center">
                <div>
                    <h2 class="text-2xl font-bold">{{ trans('trans_fleet_overview') }}</h2>
                    <p class="text-slate-500 text-sm">{{ trans('trans_fleet_subtitle') }}</p>
                </div>
                <button 
                    @click="openModal()"
                    class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-3 rounded-2xl font-bold shadow-lg shadow-indigo-500/30 transition-all flex items-center"
                >
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    {{ trans('trans_new') }}
                </button>
            </div>

            <!-- Table Card -->
            <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-100 dark:border-slate-800 shadow-sm overflow-hidden">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50 dark:bg-slate-800/50">
                            <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-slate-400">{{ trans('col_icon') || 'Icon' }}</th>
                            <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-slate-400">{{ trans('trans_col_vehicle') }}</th>
                            <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-slate-400">{{ trans('trans_col_mileage') }}</th>
                            <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-slate-400">{{ trans('trans_col_condition') }}</th>
                            <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-slate-400">{{ trans('trans_col_status') }}</th>
                            <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-slate-400 text-right">{{ trans('trans_col_actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        <tr v-for="transport in transports" :key="transport.id" class="hover:bg-slate-50 dark:hover:bg-slate-800/30 transition-colors group">
                            <td class="px-6 py-4">
                                <div class="w-10 h-10 rounded-xl bg-indigo-50 dark:bg-indigo-900/30 flex items-center justify-center text-indigo-600">
                                    <svg v-if="transport.icon_predict === 'truck'" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0" stroke-width="2" /></svg>
                                    <svg v-else-if="transport.icon_predict === 'car'" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 21a2 2 0 100-4 2 2 0 000 4zM7 10h10l1 3H6l1-3zM21 16a3 3 0 01-3 3H6a3 3 0 01-3-3l1-4h16l1 4z" /></svg>
                                    <svg v-else-if="transport.icon_predict === 'bus'" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v2m0 4v2m8-6v2m0 4v2M5 10h14a2 2 0 012 2v7a2 2 0 01-2 2H5a2 2 0 01-2-2v-7a2 2 0 012-2zM9 21v1m6-1v1" /></svg>
                                    <svg v-else-if="transport.icon_predict === 'ship'" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12l-9 7-9-7M3 10V4l9 6 9-6v6" /></svg>
                                    <svg v-else-if="transport.icon_predict === 'plane'" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z" /></svg>
                                    <svg v-else class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-semibold text-slate-900 dark:text-white">{{ transport.name }}</div>
                                <div class="text-xs text-slate-500 font-mono">{{ transport.plate_number }}</div>
                            </td>
                            <td class="px-6 py-4 text-sm font-medium">
                                {{ transport.mileage }} KM
                                <div class="text-[10px] text-slate-400">{{ trans('trans_label_oil') }}: {{ transport.last_oil_change || 'N/A' }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-xs font-bold uppercase text-slate-500">{{ trans('trans_label_tyres') }}: {{ transport.tire_condition }}</div>
                                <div class="w-24 bg-slate-100 dark:bg-slate-800 rounded-full h-1.5 mt-1 overflow-hidden">
                                    <div class="h-full bg-indigo-500" :style="{ width: transport.fuel_level + '%' }"></div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex flex-col space-y-2">
                                    <span 
                                        :class="transport.status === 'active' ? 'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400' : 'bg-rose-50 text-rose-600 dark:bg-rose-500/10 dark:text-rose-400'"
                                        class="px-3 py-1 rounded-full text-xs font-bold w-fit"
                                    >
                                        {{ transport.status.toUpperCase() }}
                                    </span>
                                    
                                    <!-- AI Health Prediction -->
                                    <div v-if="predictionResults[transport.id]" class="animate-in fade-in slide-in-from-top-1 duration-300">
                                        <div :class="{
                                            'text-emerald-500': predictionResults[transport.id].priority === 'Low',
                                            'text-amber-500': predictionResults[transport.id].priority === 'Medium',
                                            'text-rose-500': predictionResults[transport.id].priority === 'High'
                                        }" class="text-[10px] font-black uppercase flex items-center">
                                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20"><path d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v3.586L7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4a1 1 0 00-1.414-1.414L11 10.586V7z"/></svg>
                                            AI: {{ predictionResults[transport.id].priority }} PRIORITY
                                        </div>
                                        <div class="text-[9px] text-slate-400 max-w-[120px] leading-tight mt-0.5">{{ predictionResults[transport.id].next_service_estimate }}</div>
                                    </div>
                                    <button 
                                        v-else
                                        @click="predictMaintenance(transport.id)"
                                        :disabled="predictingIds.has(transport.id)"
                                        class="text-[10px] font-bold text-indigo-500 hover:text-indigo-600 flex items-center group disabled:opacity-50"
                                    >
                                        <svg :class="{'animate-spin': predictingIds.has(transport.id)}" class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                                        {{ trans('trans_predict') }}
                                    </button>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex justify-end space-x-2 transition-opacity">
                                    <button @click="openModal(transport)" class="p-2 text-indigo-500 hover:bg-indigo-50 dark:hover:bg-indigo-900/30 rounded-lg transition-all" :title="trans('btn_save')">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                    </button>
                                    <button @click="deleteTransport(transport.id)" class="p-2 text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-900/30 rounded-lg transition-all" :title="trans('btn_delete')">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="transports.length === 0">
                            <td colspan="6" class="px-6 py-12 text-center text-slate-400 italic">No transports found. Add your first vehicle to get started.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Modal -->
        <div v-if="isModalOpen" class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-slate-950/40 backdrop-blur-sm animate-in fade-in duration-200">
            <div class="bg-white dark:bg-slate-900 rounded-3xl w-full max-w-2xl shadow-2xl overflow-hidden animate-in zoom-in-95 duration-200">
                <div class="p-8 border-b border-slate-100 dark:border-slate-800 flex justify-between items-center">
                    <h3 class="text-xl font-bold">{{ editingTransport ? trans('trans_modal_edit') : trans('trans_modal_add') }}</h3>
                    <button @click="closeModal" class="text-slate-400 hover:text-slate-600 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>

                <form @submit.prevent="submit" class="p-8 space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label class="text-sm font-bold text-slate-500 uppercase tracking-wider">{{ trans('trans_label_name') }}</label>
                            <input v-model="form.name" type="text" placeholder="e.g. Scania Heavy R450" class="w-full bg-slate-50 dark:bg-slate-800 border-0 rounded-2xl p-4 focus:ring-2 focus:ring-indigo-500 transition-all font-medium">
                            <p v-if="form.errors.name" class="text-rose-500 text-xs font-bold">{{ form.errors.name }}</p>
                        </div>

                        <div class="space-y-2">
                            <label class="text-sm font-bold text-slate-500 uppercase tracking-wider">{{ trans('trans_label_plate') }}</label>
                            <input v-model="form.plate_number" type="text" placeholder="e.g. AB-123-CD" class="w-full bg-slate-50 dark:bg-slate-800 border-0 rounded-2xl p-4 focus:ring-2 focus:ring-indigo-500 transition-all font-mono uppercase">
                            <p v-if="form.errors.plate_number" class="text-rose-500 text-xs font-bold">{{ form.errors.plate_number }}</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="space-y-2">
                            <label class="text-sm font-bold text-slate-500 uppercase tracking-wider">{{ trans('trans_label_brand') }}</label>
                            <input v-model="form.brand" type="text" placeholder="e.g. Scania" class="w-full bg-slate-50 dark:bg-slate-800 border-0 rounded-2xl p-4 focus:ring-2 focus:ring-indigo-500 transition-all font-medium">
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-bold text-slate-500 uppercase tracking-wider">{{ trans('trans_label_year') }}</label>
                            <input v-model="form.model_year" type="number" placeholder="2024" class="w-full bg-slate-50 dark:bg-slate-800 border-0 rounded-2xl p-4 focus:ring-2 focus:ring-indigo-500 transition-all font-medium">
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-bold text-slate-500 uppercase tracking-wider">{{ trans('trans_col_mileage') }} (KM)</label>
                            <input v-model="form.mileage" type="number" class="w-full bg-slate-50 dark:bg-slate-800 border-0 rounded-2xl p-4 focus:ring-2 focus:ring-indigo-500 transition-all font-medium">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label class="text-sm font-bold text-slate-500 uppercase tracking-wider">{{ trans('trans_label_oil') }}</label>
                            <input v-model="form.last_oil_change" type="date" class="w-full bg-slate-50 dark:bg-slate-800 border-0 rounded-2xl p-4 focus:ring-2 focus:ring-indigo-500 transition-all font-medium">
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-bold text-slate-500 uppercase tracking-wider">{{ trans('trans_label_tyres') }} (Etat Pneu)</label>
                            <select v-model="form.tire_condition" class="w-full bg-slate-50 dark:bg-slate-800 border-0 rounded-2xl p-4 focus:ring-2 focus:ring-indigo-500 transition-all font-medium appearance-none">
                                <option value="Excellent">Excellent</option>
                                <option value="Good">Good</option>
                                <option value="Fair">Fair</option>
                                <option value="Replace Soon">Replace Soon</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="space-y-2">
                            <label class="text-sm font-bold text-slate-500 uppercase tracking-wider">{{ trans('col_icon') || 'Icon' }}</label>
                            <select v-model="form.icon_predict" class="w-full bg-slate-50 dark:bg-slate-800 border-0 rounded-2xl p-4 focus:ring-2 focus:ring-indigo-500 transition-all font-medium appearance-none">
                                <option v-for="icon in icons" :key="icon" :value="icon">{{ icon.toUpperCase() }}</option>
                            </select>
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-bold text-slate-500 uppercase tracking-wider">{{ trans('trans_col_status') }}</label>
                            <select v-model="form.status" class="w-full bg-slate-50 dark:bg-slate-800 border-0 rounded-2xl p-4 focus:ring-2 focus:ring-indigo-500 transition-all font-medium appearance-none">
                                <option value="active">ACTIVE</option>
                                <option value="maintenance">MAINTENANCE</option>
                                <option value="out_of_service">OUT OF SERVICE</option>
                            </select>
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-bold text-slate-500 uppercase tracking-wider">{{ trans('trans_label_fuel') }} (%)</label>
                            <input v-model="form.fuel_level" type="range" min="0" max="100" class="w-full h-10 bg-slate-100 dark:bg-slate-800 rounded-2xl px-4 accent-indigo-600">
                        </div>
                    </div>

                    <div class="pt-4 flex space-x-4">
                        <button type="button" @click="closeModal" class="flex-1 py-4 px-6 rounded-2xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors font-bold">{{ trans('btn_cancel') }}</button>
                        <button type="submit" :disabled="form.processing" class="flex-[2] py-4 px-6 rounded-2xl bg-indigo-600 hover:bg-indigo-700 text-white shadow-lg shadow-indigo-500/30 transition-all font-bold disabled:opacity-50">
                            {{ editingTransport ? trans('trans_update') : trans('trans_save') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
