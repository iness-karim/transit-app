<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { useTrans } from '@/Composables/useTrans';

const { trans } = useTrans();

const props = defineProps({
    drivers: Array,
});

const isModalOpen = ref(false);
const editingDriver = ref(null);

const form = useForm({
    first_name: '',
    last_name: '',
    contract_start: '',
    contract_end: '',
    status: 'active',
    phone: '',
    license_number: '',
    date_of_birth: '',
    address: '',
    emergency_contact: '',
});

const openModal = (driver = null) => {
    editingDriver.value = driver;
    if (driver) {
        form.first_name = driver.first_name || '';
        form.last_name = driver.last_name || '';
        form.contract_start = driver.contract_start || '';
        form.contract_end = driver.contract_end || '';
        form.status = driver.status || 'active';
        form.phone = driver.phone || '';
        form.license_number = driver.license_number || '';
        form.date_of_birth = driver.date_of_birth || '';
        form.address = driver.address || '';
        form.emergency_contact = driver.emergency_contact || '';
    } else {
        form.reset();
    }
    isModalOpen.value = true;
};

const submit = () => {
    if (editingDriver.value) {
        form.put(route('drivers.update', editingDriver.value.id), {
            onSuccess: () => closeModal(),
        });
    } else {
        form.post(route('drivers.store'), {
            onSuccess: () => closeModal(),
        });
    }
};

const closeModal = () => {
    isModalOpen.value = false;
    editingDriver.value = null;
    form.reset();
};

const deleteDriver = (id) => {
    if (confirm(trans('confirm_delete'))) {
        useForm({}).delete(route('drivers.destroy', id));
    }
};

const healthResults = ref({});
const predictingIds = ref(new Set());

const predictHealth = async (id) => {
    predictingIds.value.add(id);
    try {
        const response = await fetch(route('drivers.predict', id));
        const data = await response.json();
        healthResults.value[id] = data;
    } catch (e) {
        console.error('Prediction failed', e);
    } finally {
        predictingIds.value.delete(id);
    }
};
</script>

<template>
    <Head :title="trans('nav_drivers')" />

    <AuthenticatedLayout>
        <template #header>
            {{ trans('drivers_title') }}
        </template>

        <div class="space-y-6">
            <!-- Top Actions -->
            <div class="flex justify-between items-center">
                <div>
                    <h2 class="text-2xl font-bold">{{ trans('drivers_roster') }}</h2>
                    <p class="text-slate-500 text-sm">{{ trans('drivers_subtitle') }}</p>
                </div>
                <button 
                    @click="openModal()"
                    class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-3 rounded-2xl font-bold shadow-lg shadow-indigo-500/30 transition-all flex items-center"
                >
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    {{ trans('drivers_new') }}
                </button>
            </div>

            <!-- Table Card -->
            <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-100 dark:border-slate-800 shadow-sm overflow-hidden text-slate-800 dark:text-slate-200">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50 dark:bg-slate-800/50">
                            <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-slate-400">{{ trans('drivers_col_driver') }}</th>
                            <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-slate-400">{{ trans('drivers_col_pro') }}</th>
                            <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-slate-400">{{ trans('drivers_col_health') }}</th>
                            <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-slate-400">{{ trans('trans_col_status') }}</th>
                            <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-slate-400 text-right">{{ trans('trans_col_actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        <tr v-for="driver in drivers" :key="driver.id" class="hover:bg-slate-50 dark:hover:bg-slate-800/30 transition-colors group">
                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    <div class="w-10 h-10 rounded-full bg-indigo-50 dark:bg-indigo-900/30 flex items-center justify-center text-indigo-600 font-bold mr-3 uppercase">
                                        {{ driver.first_name ? driver.first_name[0] : '' }}{{ driver.last_name ? driver.last_name[0] : '' }}
                                    </div>
                                    <div>
                                        <p class="font-semibold">{{ driver.first_name }} {{ driver.last_name }}</p>
                                        <p class="text-xs text-slate-400">{{ driver.phone || 'No Phone' }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm">
                                <div class="font-medium text-slate-700 dark:text-slate-300">Lic: {{ driver.license_number || 'N/A' }}</div>
                                <div class="text-[10px] text-slate-400 font-mono italic">{{ driver.address || 'No Address' }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <div v-if="healthResults[driver.id]" class="animate-in fade-in slide-in-from-right-1 duration-300">
                                    <div class="flex items-center space-x-2">
                                        <div :class="{
                                            'bg-emerald-500': healthResults[driver.id].health_score > 80,
                                            'bg-amber-500': healthResults[driver.id].health_score <= 80 && healthResults[driver.id].health_score > 40,
                                            'bg-rose-500': healthResults[driver.id].health_score <= 40
                                        }" class="px-2 py-0.5 rounded text-[10px] font-black text-white">
                                            {{ healthResults[driver.id].health_score }}% READY
                                        </div>
                                        <div class="text-[10px] font-bold text-slate-500 uppercase">{{ healthResults[driver.id].fatigue_level }}</div>
                                    </div>
                                    <div class="text-[9px] text-slate-400 mt-1 max-w-[150px] italic">"{{ healthResults[driver.id].recommendation }}"</div>
                                </div>
                                <button 
                                    v-else
                                    @click="predictHealth(driver.id)"
                                    :disabled="predictingIds.has(driver.id)"
                                    class="bg-indigo-50 dark:bg-indigo-900/20 text-indigo-600 dark:text-indigo-400 px-3 py-1.5 rounded-xl text-[10px] font-bold flex items-center hover:bg-indigo-100 transition-all disabled:opacity-50"
                                >
                                    <svg :class="{'animate-spin': predictingIds.has(driver.id)}" class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" /></svg>
                                    {{ trans('drivers_predict') }}
                                </button>
                            </td>
                            <td class="px-6 py-4">
                                <span 
                                    :class="driver.status === 'active' ? 'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400' : 'bg-rose-50 text-rose-600 dark:bg-rose-500/10 dark:text-rose-400'"
                                    class="px-3 py-1 rounded-full text-xs font-bold"
                                >
                                    {{ driver.status.toUpperCase() }}
                                </span>
                            </td>
                             <td class="px-6 py-4 text-right">
                                <div class="flex justify-end space-x-2 transition-opacity">
                                    <button @click="openModal(driver)" class="p-2 text-indigo-500 hover:bg-indigo-50 dark:hover:bg-indigo-900/30 rounded-lg transition-all" :title="trans('btn_save')">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                    </button>
                                    <button @click="deleteDriver(driver.id)" class="p-2 text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-900/30 rounded-lg transition-all" :title="trans('btn_delete')">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="drivers.length === 0">
                            <td colspan="5" class="px-6 py-12 text-center text-slate-400 italic">No drivers registered yet.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Modal -->
        <div v-if="isModalOpen" class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-slate-950/40 backdrop-blur-sm animate-in fade-in duration-200">
            <div class="bg-white dark:bg-slate-900 rounded-3xl w-full max-w-2xl shadow-2xl overflow-hidden animate-in zoom-in-95 duration-200 text-slate-800 dark:text-slate-200">
                <div class="p-8 border-b border-slate-100 dark:border-slate-800 flex justify-between items-center text-slate-800 dark:text-slate-200">
                    <h3 class="text-xl font-bold">{{ editingDriver ? trans('drivers_modal_edit') : trans('drivers_modal_add') }}</h3>
                    <button @click="closeModal" class="text-slate-400 hover:text-slate-600 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>

                <form @submit.prevent="submit" class="p-8 space-y-6">
                    <div class="grid grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label class="text-sm font-bold text-slate-500 uppercase tracking-wider">{{ trans('drivers_label_fname') }}</label>
                            <input v-model="form.first_name" type="text" class="w-full bg-slate-50 dark:bg-slate-800 border-0 rounded-2xl p-4 focus:ring-2 focus:ring-indigo-500 transition-all font-medium text-slate-800 dark:text-slate-200">
                            <p v-if="form.errors.first_name" class="text-rose-500 text-xs font-bold">{{ form.errors.first_name }}</p>
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-bold text-slate-500 uppercase tracking-wider">{{ trans('drivers_label_lname') }}</label>
                            <input v-model="form.last_name" type="text" class="w-full bg-slate-50 dark:bg-slate-800 border-0 rounded-2xl p-4 focus:ring-2 focus:ring-indigo-500 transition-all font-medium text-slate-800 dark:text-slate-200">
                            <p v-if="form.errors.last_name" class="text-rose-500 text-xs font-bold">{{ form.errors.last_name }}</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label class="text-sm font-bold text-slate-500 uppercase tracking-wider">{{ trans('drivers_label_phone') }}</label>
                            <input v-model="form.phone" type="tel" class="w-full bg-slate-50 dark:bg-slate-800 border-0 rounded-2xl p-4 focus:ring-2 focus:ring-indigo-500 transition-all font-medium text-slate-800 dark:text-slate-200">
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-bold text-slate-500 uppercase tracking-wider">{{ trans('drivers_label_license') }}</label>
                            <input v-model="form.license_number" type="text" class="w-full bg-slate-50 dark:bg-slate-800 border-0 rounded-2xl p-4 focus:ring-2 focus:ring-indigo-500 transition-all font-medium text-slate-800 dark:text-slate-200">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label class="text-sm font-bold text-slate-500 uppercase tracking-wider">{{ trans('drivers_label_start') }}</label>
                            <input v-model="form.contract_start" type="date" class="w-full bg-slate-50 dark:bg-slate-800 border-0 rounded-2xl p-4 focus:ring-2 focus:ring-indigo-500 transition-all font-medium text-slate-800 dark:text-slate-200">
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-bold text-slate-500 uppercase tracking-wider">{{ trans('drivers_label_end') }}</label>
                            <input v-model="form.contract_end" type="date" class="w-full bg-slate-50 dark:bg-slate-800 border-0 rounded-2xl p-4 focus:ring-2 focus:ring-indigo-500 transition-all font-medium text-slate-800 dark:text-slate-200">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label class="text-sm font-bold text-slate-500 uppercase tracking-wider">{{ trans('drivers_label_dob') }}</label>
                            <input v-model="form.date_of_birth" type="date" class="w-full bg-slate-50 dark:bg-slate-800 border-0 rounded-2xl p-4 focus:ring-2 focus:ring-indigo-500 transition-all font-medium text-slate-800 dark:text-slate-200">
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-bold text-slate-500 uppercase tracking-wider">{{ trans('drivers_label_emergency') }}</label>
                            <input v-model="form.emergency_contact" type="text" placeholder="Name & Phone" class="w-full bg-slate-50 dark:bg-slate-800 border-0 rounded-2xl p-4 focus:ring-2 focus:ring-indigo-500 transition-all font-medium text-slate-800 dark:text-slate-200">
                        </div>
                    </div>

                    <div class="space-y-2">
                        <label class="text-sm font-bold text-slate-500 uppercase tracking-wider">{{ trans('drivers_label_address') }}</label>
                        <textarea v-model="form.address" rows="2" class="w-full bg-slate-50 dark:bg-slate-800 border-0 rounded-2xl p-4 focus:ring-2 focus:ring-indigo-500 transition-all font-medium text-slate-800 dark:text-slate-200"></textarea>
                    </div>

                    <div class="space-y-2">
                        <label class="text-sm font-bold text-slate-500 uppercase tracking-wider">{{ trans('drivers_label_status') }}</label>
                        <select v-model="form.status" class="w-full bg-slate-50 dark:bg-slate-800 border-0 rounded-2xl p-4 focus:ring-2 focus:ring-indigo-500 transition-all font-medium appearance-none text-slate-800 dark:text-slate-200">
                            <option value="active">Active</option>
                            <option value="on_leave">On Leave</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>

                    <div class="pt-4 flex space-x-4">
                        <button type="button" @click="closeModal" class="flex-1 py-4 px-6 rounded-2xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors font-bold text-slate-800 dark:text-slate-200">{{ trans('btn_cancel') }}</button>
                        <button type="submit" :disabled="form.processing" class="flex-[2] py-4 px-6 rounded-2xl bg-indigo-600 hover:bg-indigo-700 text-white shadow-lg shadow-indigo-500/30 transition-all font-bold disabled:opacity-50">
                            {{ editingDriver ? trans('drivers_update') : trans('drivers_save') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
