<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { format } from 'date-fns';

const props = defineProps({
    deliveries: Array
});

const getPunctuality = (delivery) => {
    if (!delivery.actual_arrival || !delivery.estimated_arrival) return { label: 'Inconnu', color: 'text-slate-400' };
    
    const actual = new Date(delivery.actual_arrival);
    const estimated = new Date(delivery.estimated_arrival);
    
    if (actual <= estimated) {
        return { label: 'À Temps', color: 'text-emerald-500 bg-emerald-500/10 border-emerald-500/20' };
    } else {
        return { label: 'En Retard', color: 'text-rose-500 bg-rose-500/10 border-rose-500/20' };
    }
};

const formatDate = (dateString) => {
    if (!dateString) return '—';
    try {
        return format(new Date(dateString), 'dd MMM yyyy, HH:mm');
    } catch (e) {
        return dateString;
    }
};
</script>

<template>
    <Head title="Suivi des Livraisons" />

    <AuthenticatedLayout>
        <template #header>
            Suivi des Livraisons & Ponctualité
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-slate-900 overflow-hidden shadow-2xl rounded-[2.5rem] border border-slate-100 dark:border-slate-800">
                    <div class="p-10">
                        <div class="flex items-center justify-between mb-10">
                            <div>
                                <h2 class="text-3xl font-black text-slate-800 dark:text-white mb-2">Historique des Trajets</h2>
                                <p class="text-slate-500 dark:text-slate-400 font-medium">Analyse de la performance et de la ponctualité de votre flotte.</p>
                            </div>
                            <div class="flex gap-4">
                                <span class="px-4 py-2 bg-slate-50 dark:bg-slate-800 rounded-2xl text-xs font-bold text-slate-600 dark:text-slate-300 border border-slate-100 dark:border-slate-700">
                                    {{ deliveries.length }} Trajets Terminés
                                </span>
                            </div>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse">
                                <thead>
                                    <tr class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 border-b border-slate-50 dark:border-slate-800">
                                        <th class="pb-4 px-4">Véhicule & Chauffeur</th>
                                        <th class="pb-4 px-4">Destination</th>
                                        <th class="pb-4 px-4">Planifié</th>
                                        <th class="pb-4 px-4">Réalisé</th>
                                        <th class="pb-4 px-4">Status</th>
                                        <th class="pb-4 px-4 text-right">Punctualité</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-50 dark:divide-slate-800">
                                    <tr v-for="delivery in deliveries" :key="delivery.id" class="group hover:bg-slate-50/50 dark:hover:bg-slate-800/30 transition-colors">
                                        <td class="py-5 px-4">
                                            <div class="flex items-center">
                                                <div class="w-10 h-10 rounded-xl bg-indigo-50 dark:bg-indigo-900/30 flex items-center justify-center text-lg mr-4">
                                                    {{ delivery.status === 'completed' ? '✅' : '❌' }}
                                                </div>
                                                <div>
                                                    <p class="font-black text-slate-800 dark:text-slate-200">{{ delivery.transport?.brand }} ({{ delivery.transport?.plate_number }})</p>
                                                    <p class="text-xs text-slate-400 font-medium">{{ delivery.driver?.first_name }} {{ delivery.driver?.last_name }}</p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="py-5 px-4">
                                            <p class="font-bold text-slate-700 dark:text-slate-300">{{ delivery.end_location }}</p>
                                            <p class="text-[10px] text-slate-400 font-bold uppercase">{{ delivery.cargo_type || 'General' }}</p>
                                        </td>
                                        <td class="py-5 px-4 text-sm text-slate-500 dark:text-slate-400">
                                            {{ formatDate(delivery.estimated_arrival) }}
                                        </td>
                                        <td class="py-5 px-4 text-sm font-bold text-indigo-600 dark:text-indigo-400">
                                            {{ formatDate(delivery.actual_arrival) }}
                                        </td>
                                        <td class="py-5 px-4">
                                            <span :class="[
                                                'px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest border',
                                                delivery.status === 'completed' ? 'bg-emerald-500/10 text-emerald-500 border-emerald-500/20' : 'bg-rose-500/10 text-rose-500 border-rose-500/20'
                                            ]">
                                                {{ delivery.status }}
                                            </span>
                                        </td>
                                        <td class="py-5 px-4 text-right">
                                            <span v-if="delivery.status === 'completed'" :class="['px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest border inline-block', getPunctuality(delivery).color]">
                                                {{ getPunctuality(delivery).label }}
                                            </span>
                                            <span v-else class="text-[10px] font-black text-slate-300 uppercase italic">Annulé</span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div v-if="deliveries.length === 0" class="py-20 text-center">
                            <div class="text-6xl mb-4">📦</div>
                            <h3 class="text-xl font-black text-slate-800 dark:text-white mb-2">Aucune livraison encore effectuée</h3>
                            <p class="text-slate-500 dark:text-slate-400">Complétez votre premier trajet pour voir les données ici.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
