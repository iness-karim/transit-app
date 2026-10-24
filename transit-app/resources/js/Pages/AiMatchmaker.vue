<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    unassigned_routes: Array,
    available_drivers: Array,
});

const selectedRoute = ref(null);
const recommendations = ref(null);
const isLoading = ref(false);
const briefing = ref('');

const fetchRecommendations = async () => {
    if (!selectedRoute.value) return;
    
    isLoading.value = true;
    recommendations.value = null;
    
    try {
        const res = await fetch(route('routes.predict', selectedRoute.value.id)); // Using predict as base since it has AI service injected
        // Actually, I need a specific endpoint for matchmaking
        const matchmakingRes = await fetch(`/ai-hub/matchmaker/recommend/${selectedRoute.value.id}`);
        const data = await matchmakingRes.json();
        recommendations.value = data.recommendations;
        briefing.value = data.dispatch_briefing;
    } catch (e) {
        console.error('Matchmaking failed:', e);
    } finally {
        isLoading.value = false;
    }
};

const selectRoute = (route) => {
    selectedRoute.value = route;
    recommendations.value = null;
    briefing.value = '';
};
</script>

<template>
    <Head title="AI Driver-Mission Matchmaker" />

    <AuthenticatedLayout>
        <template #header>
            AI Driver-Mission Matchmaker
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    
                    <!-- Left Column: Unassigned Routes -->
                    <div class="lg:col-span-1 space-y-6">
                        <h3 class="text-xl font-black text-slate-800 dark:text-white flex items-center">
                            <span class="mr-3">📍</span> Unassigned Routes
                        </h3>
                        
                        <div v-if="unassigned_routes.length === 0" class="p-8 bg-white dark:bg-slate-900 rounded-[2rem] border border-slate-100 dark:border-slate-800 text-center">
                            <p class="text-slate-500 font-medium">No open routes available.</p>
                        </div>
                        
                        <div v-for="route in unassigned_routes" :key="route.id" 
                            @click="selectRoute(route)"
                            :class="[
                                'p-6 bg-white dark:bg-slate-900 rounded-[2rem] border cursor-pointer transition-all duration-300',
                                selectedRoute?.id === route.id ? 'border-indigo-500 ring-2 ring-indigo-500/20 shadow-xl' : 'border-slate-100 dark:border-slate-800 hover:border-indigo-300'
                            ]"
                        >
                            <div class="flex justify-between items-start mb-4">
                                <span class="px-3 py-1 bg-indigo-50 dark:bg-indigo-900/30 text-[10px] font-black text-indigo-600 dark:text-indigo-400 rounded-lg uppercase tracking-widest">Route #{{ route.id }}</span>
                                <span class="text-xs font-bold text-slate-400">{{ route.total_distance }} KM</span>
                            </div>
                            <p class="font-black text-slate-800 dark:text-white leading-tight mb-1">{{ route.start_location }}</p>
                            <p class="font-black text-indigo-500 leading-tight mb-3">→ {{ route.end_location }}</p>
                            <p class="text-xs text-slate-400 font-medium">{{ route.cargo_type || 'General Cargo' }}</p>
                        </div>
                    </div>

                    <!-- Right Column: AI Analysis -->
                    <div class="lg:col-span-2">
                        <div class="bg-gradient-to-br from-slate-900 via-indigo-950 to-black rounded-[2.5rem] p-10 text-white shadow-2xl relative overflow-hidden h-full min-h-[600px]">
                            
                            <div v-if="!selectedRoute" class="h-full flex flex-col items-center justify-center text-center py-20">
                                <span class="text-6xl mb-6">🧠</span>
                                <h3 class="text-2xl font-black mb-4">AI Dispatch Engine Ready</h3>
                                <p class="text-indigo-200/60 max-w-md font-medium">Select a route from the left to begin the elite driver matchmaking process.</p>
                            </div>

                            <div v-else class="relative z-10">
                                <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-12">
                                    <div>
                                        <h3 class="text-3xl font-black mb-2">Analyzing Mission #{{ selectedRoute.id }}</h3>
                                        <p class="text-indigo-300 font-medium">Targeting 100% mission success rate.</p>
                                    </div>
                                    <button 
                                        @click="fetchRecommendations"
                                        :disabled="isLoading"
                                        class="px-8 py-4 bg-indigo-600 hover:bg-indigo-500 disabled:bg-slate-700 rounded-2xl font-black uppercase tracking-widest transition-all shadow-lg shadow-indigo-500/30 flex items-center gap-3"
                                    >
                                        {{ isLoading ? 'Computing...' : 'Find Best Driver' }}
                                        <span v-if="!isLoading">🚀</span>
                                    </button>
                                </div>

                                <!-- AI recommendations -->
                                <div v-if="recommendations" class="space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-700">
                                    <div class="p-6 bg-white/5 border border-white/10 rounded-3xl backdrop-blur-md">
                                        <p class="text-indigo-200 italic leading-relaxed font-medium">"{{ briefing }}"</p>
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <div v-for="rec in recommendations" :key="rec.driver_id" class="p-6 bg-gradient-to-br from-indigo-900/40 to-slate-900/40 border border-indigo-500/20 rounded-[2rem] relative group hover:border-indigo-400/40 transition-all">
                                            <div class="flex items-center justify-between mb-4">
                                                <div class="flex items-center gap-3">
                                                    <div class="w-10 h-10 bg-indigo-500 rounded-xl flex items-center justify-center font-black">
                                                        {{ rec.driver_id }}
                                                    </div>
                                                    <div>
                                                        <p class="font-black text-lg">Candidate Rank</p>
                                                        <p class="text-xs text-indigo-300 font-bold uppercase tracking-widest">Driver Profile #{{ rec.driver_id }}</p>
                                                    </div>
                                                </div>
                                                <div class="text-2xl font-black text-indigo-400">
                                                    {{ rec.match_score }}%
                                                </div>
                                            </div>
                                            <p class="text-sm text-indigo-100/70 font-medium leading-relaxed mb-6">{{ rec.reasoning }}</p>
                                            
                                            <Link :href="route('routes.index')" class="w-full py-3 bg-white/10 hover:bg-white/20 rounded-xl text-center text-xs font-black uppercase tracking-tighter transition-all">
                                                Go to Assignments
                                            </Link>
                                        </div>
                                    </div>
                                </div>

                                <!-- Loading state -->
                                <div v-if="isLoading" class="space-y-6 pt-12">
                                    <div class="h-4 w-1/2 bg-white/10 rounded-full animate-pulse"></div>
                                    <div class="grid grid-cols-2 gap-6">
                                        <div class="h-48 bg-white/5 rounded-[2rem] animate-pulse"></div>
                                        <div class="h-48 bg-white/5 rounded-[2rem] animate-pulse"></div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
