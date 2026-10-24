<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { useTrans } from '@/Composables/useTrans';

const { trans } = useTrans();

const props = defineProps({
    drivers: Array
});

const isModalOpen = ref(false);
const selectedDriver = ref(null);

const form = useForm({
    email: '',
    password: '',
});

const openModal = (driver) => {
    selectedDriver.value = driver;
    form.email = '';
    form.password = '';
    isModalOpen.value = true;
};

const submit = () => {
    form.post(route('access.store', selectedDriver.value.id), {
        onSuccess: () => {
            isModalOpen.value = false;
            form.reset();
        },
    });
};

const revokeAccess = (driver) => {
    if (confirm(`Revoke access for ${driver.first_name}?`)) {
        useForm({}).delete(route('access.destroy', driver.id));
    }
};
</script>

<template>
    <Head title="Access Control" />

    <AuthenticatedLayout>
        <template #header>
            Driver Access Control
        </template>

        <div class="space-y-6">
            <div class="flex justify-between items-center">
                <div>
                    <h2 class="text-2xl font-bold">Manage Driver Access</h2>
                    <p class="text-slate-500 text-sm">Create and manage login credentials for your fleet chauffeurs.</p>
                </div>
            </div>

            <!-- Notifications -->
            <div v-if="$page.props.flash?.success" class="bg-emerald-50 border border-emerald-100 text-emerald-600 px-6 py-4 rounded-2xl font-bold animate-in fade-in slide-in-from-top-2 duration-300">
                {{ $page.props.flash.success }}
            </div>

            <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-100 dark:border-slate-800 shadow-sm overflow-hidden">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50 dark:bg-slate-800/50">
                            <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-slate-400">Chauffeur</th>
                            <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-slate-400">Account Status</th>
                            <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-slate-400">Email / Login</th>
                            <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-slate-400 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50 dark:divide-slate-800">
                        <tr v-for="driver in drivers" :key="driver.id" class="hover:bg-slate-50/50 dark:hover:bg-slate-800/30 transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    <div class="w-10 h-10 rounded-full bg-indigo-50 dark:bg-indigo-900/30 flex items-center justify-center text-indigo-600 font-bold mr-3 uppercase">
                                        {{ driver.first_name ? driver.first_name[0] : '' }}{{ driver.last_name ? driver.last_name[0] : '' }}
                                    </div>
                                    <span class="font-bold text-slate-700 dark:text-slate-200">{{ driver.first_name }} {{ driver.last_name }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span v-if="driver.user" class="bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400 px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest">
                                    ACTIVE ACCESS
                                </span>
                                <span v-else class="bg-slate-100 text-slate-400 dark:bg-slate-800 dark:text-slate-600 px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest">
                                    NO LOGIN
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-sm font-medium text-slate-500">{{ driver.user?.email || '—' }}</span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <button v-if="!driver.user" @click="openModal(driver)" class="text-indigo-600 hover:text-indigo-700 font-black text-xs uppercase tracking-widest">
                                    Grant Access
                                </button>
                                <button v-else @click="revokeAccess(driver)" class="text-rose-500 hover:text-rose-600 font-black text-xs uppercase tracking-widest">
                                    Revoke
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Grant Access Modal -->
        <div v-if="isModalOpen" class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-slate-950/40 backdrop-blur-sm">
            <div class="bg-white dark:bg-slate-900 rounded-3xl w-full max-w-md shadow-2xl overflow-hidden animate-in zoom-in-95 duration-200">
                <div class="p-8 border-b border-slate-100 dark:border-slate-800 flex justify-between items-center">
                    <h3 class="text-xl font-bold">Grant Access: {{ selectedDriver?.first_name }}</h3>
                    <button @click="isModalOpen = false" class="text-slate-400 hover:text-slate-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>
                <form @submit.prevent="submit" class="p-8 space-y-4">
                    <div class="space-y-2">
                        <label class="text-xs font-black text-slate-400 uppercase tracking-widest">Login Email</label>
                        <input v-model="form.email" type="email" required placeholder="driver@transithub.com" class="w-full bg-slate-50 dark:bg-slate-800 border-0 rounded-2xl p-4 focus:ring-2 focus:ring-indigo-500 transition-all font-medium">
                        <div v-if="form.errors.email" class="text-rose-500 text-xs font-bold">{{ form.errors.email }}</div>
                    </div>
                    <div class="space-y-2">
                        <label class="text-xs font-black text-slate-400 uppercase tracking-widest">Temporary Password</label>
                        <input v-model="form.password" type="password" required placeholder="Minimum 6 characters" class="w-full bg-slate-50 dark:bg-slate-800 border-0 rounded-2xl p-4 focus:ring-2 focus:ring-indigo-500 transition-all font-medium">
                        <div v-if="form.errors.password" class="text-rose-500 text-xs font-bold">{{ form.errors.password }}</div>
                    </div>
                    <button type="submit" :disabled="form.processing" class="w-full py-4 bg-indigo-600 text-white rounded-2xl font-black uppercase tracking-widest shadow-lg shadow-indigo-500/30 hover:bg-indigo-700 transition-all disabled:opacity-50 mt-4">
                        Initialize Account
                    </button>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
