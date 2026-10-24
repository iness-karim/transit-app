<script setup>
import { defineProps } from 'vue';

const props = defineProps({
    route: {
        type: Object,
        required: true
    }
});

const formatDate = (dateString) => {
    if (!dateString) return 'N/A';
    const date = new Date(dateString);
    return date.toLocaleDateString('fr-FR', { 
        day: '2-digit', month: '2-digit', year: 'numeric',
        hour: '2-digit', minute: '2-digit'
    });
};
</script>

<template>
    <div class="bg-white text-slate-800 p-10 font-sans w-[800px] h-[1130px] mx-auto relative shadow-sm border border-slate-100" id="delivery-note-container">
        <!-- Header -->
        <div class="flex justify-between items-start border-b-2 border-indigo-600 pb-6 mb-8">
            <div>
                <h1 class="text-3xl font-black text-indigo-600 tracking-tight">STN RAGHD TMS</h1>
                <p class="text-sm text-slate-500 font-medium">Transportation Management System</p>
            </div>
            <div class="text-right text-xs text-slate-500 space-y-1">
                <p><span class="font-bold text-slate-700">Date Génération:</span> {{ formatDate(new Date()) }}</p>
                <p><span class="font-bold text-slate-700">ID Document:</span> BL-{{ new Date().getFullYear() }}-{{ String(route.id).padStart(5, '0') }}</p>
                <p>123 Avenue Hasan II, Casablanca, Maroc</p>
            </div>
        </div>

        <!-- Title -->
        <div class="text-center mb-10">
            <h2 class="text-2xl font-bold text-slate-800 tracking-wide uppercase">Bon de Livraison</h2>
            <div class="inline-block mt-2 px-3 py-1 rounded bg-emerald-100 text-emerald-800 text-xs font-bold uppercase tracking-wider border border-emerald-200">
                STATUT: {{ route.status }}
            </div>
        </div>

        <!-- Mission Details -->
        <div class="mb-8">
            <h3 class="text-sm font-bold text-slate-400 uppercase tracking-widest border-b border-slate-200 pb-2 mb-4">Détails de la Mission</h3>
            <div class="grid grid-cols-2 gap-6 bg-slate-50 p-6 rounded-xl border border-slate-100">
                <div>
                    <p class="text-xs font-bold text-slate-400 uppercase">Origine</p>
                    <p class="text-base font-bold text-slate-800 mt-1">{{ route.start_location }}</p>
                </div>
                <div>
                    <p class="text-xs font-bold text-slate-400 uppercase">Destination</p>
                    <p class="text-base font-bold text-slate-800 mt-1">{{ route.end_location }}</p>
                </div>
                <div>
                    <p class="text-xs font-bold text-slate-400 uppercase">Distance Totale</p>
                    <p class="text-base font-bold text-indigo-600 mt-1">{{ route.total_distance }} KM</p>
                </div>
                <div>
                    <p class="text-xs font-bold text-slate-400 uppercase">Type de Cargaison</p>
                    <p class="text-base font-medium text-slate-800 mt-1">{{ route.cargo_type || 'Générale' }}</p>
                </div>
            </div>
        </div>

        <!-- Actors -->
        <div class="mb-10">
            <h3 class="text-sm font-bold text-slate-400 uppercase tracking-widest border-b border-slate-200 pb-2 mb-4">Intervenants & Équipement</h3>
            <div class="grid grid-cols-2 gap-6">
                <!-- Driver -->
                <div class="border border-slate-200 p-5 rounded-xl">
                    <div class="flex items-center space-x-3 mb-3">
                        <div class="w-10 h-10 bg-indigo-100 rounded-full flex items-center justify-center text-indigo-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                        </div>
                        <div>
                            <p class="text-xs font-bold text-slate-400 uppercase">Chauffeur Assigné</p>
                            <p class="font-bold text-slate-800">{{ route.driver ? route.driver.first_name + ' ' + route.driver.last_name : 'Non assigné' }}</p>
                        </div>
                    </div>
                    <div class="text-xs text-slate-600 space-y-1">
                        <p><span class="font-medium">Tél:</span> {{ route.driver ? route.driver.phone : 'N/A' }}</p>
                        <p><span class="font-medium">Permis:</span> {{ route.driver ? route.driver.license_number : 'N/A' }}</p>
                    </div>
                </div>

                <!-- Transport -->
                <div class="border border-slate-200 p-5 rounded-xl">
                    <div class="flex items-center space-x-3 mb-3">
                        <div class="w-10 h-10 bg-teal-100 rounded-full flex items-center justify-center text-teal-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"></path></svg>
                        </div>
                        <div>
                            <p class="text-xs font-bold text-slate-400 uppercase">Véhicule</p>
                            <p class="font-bold text-slate-800">{{ route.transport ? route.transport.name : 'Non assigné' }}</p>
                        </div>
                    </div>
                    <div class="text-xs text-slate-600 space-y-1">
                        <p><span class="font-medium">Immatriculation:</span> <span class="font-mono bg-slate-100 px-1 rounded">{{ route.transport ? route.transport.plate_number : 'N/A' }}</span></p>
                        <p><span class="font-medium">Marque:</span> {{ route.transport ? route.transport.brand : 'N/A' }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Metrics -->
        <div class="mb-12">
             <h3 class="text-sm font-bold text-slate-400 uppercase tracking-widest border-b border-slate-200 pb-2 mb-4">Conditions & Chronologie</h3>
             <table class="w-full text-sm text-left">
                <thead class="bg-slate-50 text-slate-500 uppercase text-xs font-bold">
                    <tr>
                        <th class="px-4 py-3 rounded-l-lg">Météo Constatée</th>
                        <th class="px-4 py-3">État du Trafic</th>
                        <th class="px-4 py-3 rounded-r-lg">Date de Création</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="border-b border-slate-100">
                        <td class="px-4 py-4 font-medium">{{ route.weather || 'Standard' }}</td>
                        <td class="px-4 py-4 font-medium">{{ route.traffic_status || 'Fluide' }}</td>
                        <td class="px-4 py-4 text-slate-500">{{ formatDate(route.created_at) }}</td>
                    </tr>
                </tbody>
             </table>
        </div>

        <!-- Signatures -->
        <div class="absolute bottom-24 left-10 right-10">
            <div class="grid grid-cols-3 gap-8 text-center pt-8 border-t-2 border-slate-200">
                <div>
                    <p class="text-xs font-bold text-slate-500 uppercase mb-16">Le Chauffeur</p>
                    <p class="text-[10px] text-slate-400 border-t border-slate-200 pt-2 w-32 mx-auto">(Signature et Date)</p>
                </div>
                <div>
                    <p class="text-xs font-bold text-slate-500 uppercase mb-16">RAGHD TMS (Logistique)</p>
                    <p class="text-[10px] text-slate-400 border-t border-slate-200 pt-2 w-32 mx-auto">(Cachet de l'entreprise)</p>
                </div>
                <div>
                    <p class="text-xs font-bold text-indigo-600 uppercase mb-16">Le Client / Réception</p>
                    <p class="text-[10px] text-slate-400 border-t border-slate-200 pt-2 w-32 mx-auto">(Signature et Cachet)</p>
                </div>
            </div>
        </div>

        <div class="absolute bottom-6 left-0 right-0 text-center text-[10px] text-slate-400">
            Document généré automatiquement par STN RAGHD TMS - Le système intelligent de logistique au Maroc.
        </div>
    </div>
</template>
