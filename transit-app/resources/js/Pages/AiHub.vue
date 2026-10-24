<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const aiFeatures = [
    {
        title: 'Fleet Intelligence Pulse',
        description: 'Global strategic briefing and real-time fleet health monitoring.',
        status: 'Active',
        icon: '🌐',
        color: 'from-blue-500 to-indigo-600',
        link: route('dashboard')
    },
    {
        title: 'Predictive Maintenance',
        description: 'AI-driven forecasting for vehicle service intervals and breakdown prevention.',
        status: 'Active',
        icon: '🛠️',
        color: 'from-rose-500 to-orange-600',
        link: route('dashboard')
    },
    {
        title: 'Mission Risk Analysis',
        description: 'Deep logistical analysis for individual trajets, including weather and traffic impact.',
        status: 'Active',
        icon: '📋',
        color: 'from-emerald-500 to-teal-600',
        link: route('routes.index')
    },
    {
        title: 'Driver-Mission Matchmaker',
        description: 'Elite AI engine to pair the best drivers with the most complex missions based on performance data.',
        status: 'Active',
        icon: '🧠',
        color: 'from-purple-500 to-indigo-600',
        link: route('ai.matchmaker')
    }
];
</script>

<template>
    <Head title="AI Intelligence Hub" />

    <AuthenticatedLayout>
        <template #header>
            AI Intelligence Hub
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Hero Section -->
                <div class="bg-gradient-to-br from-indigo-900 via-slate-900 to-black rounded-[3rem] p-12 text-white shadow-2xl relative overflow-hidden mb-12 border border-white/10">
                    <div class="relative z-10">
                        <span class="px-4 py-1.5 bg-indigo-500/20 border border-indigo-400/30 rounded-full text-xs font-black uppercase tracking-[0.2em] text-indigo-300 mb-6 inline-block">Enterprise Intelligence</span>
                        <h2 class="text-5xl font-black mb-6 leading-tight">Your Fleet, <br><span class="text-transparent bg-clip-text bg-gradient-to-r from-indigo-400 to-cyan-400">Powered by AI.</span></h2>
                        <p class="text-indigo-100/70 text-xl max-w-2xl font-medium leading-relaxed">RAGHD TMS AI analyzes millions of data points every second to ensure your operations are safer, faster, and more efficient than ever before.</p>
                    </div>
                    
                    <!-- Abstract Background -->
                    <div class="absolute top-0 right-0 w-1/2 h-full opacity-20 pointer-events-none">
                        <svg viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg" class="w-full h-full scale-150">
                            <path fill="#4F46E5" d="M47.7,-62.4C60.2,-52.9,67.6,-37.2,71.2,-20.9C74.7,-4.6,74.3,12.3,67.6,26.5C60.9,40.7,47.9,52.2,33.3,58.8C18.6,65.3,2.4,67,-14.2,63.9C-30.8,60.8,-47.8,53,-57.8,39.9C-67.8,26.7,-70.7,8.2,-66.9,-8C-63,-24.2,-52.3,-38.2,-40.1,-47.8C-27.8,-57.4,-13.9,-62.6,2.1,-65.4C18,-68.2,35.3,-71.8,47.7,-62.4Z" transform="translate(100 100)" />
                        </svg>
                    </div>
                </div>

                <!-- Features Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div v-for="feature in aiFeatures" :key="feature.title" 
                        class="group relative bg-white dark:bg-slate-900 rounded-[2.5rem] p-8 border border-slate-100 dark:border-slate-800 shadow-sm hover:shadow-2xl hover:-translate-y-2 transition-all duration-500 overflow-hidden"
                    >
                        <!-- Background Glow -->
                        <div :class="['absolute -top-24 -right-24 h-48 w-48 rounded-full blur-3xl opacity-0 group-hover:opacity-10 transition-opacity bg-gradient-to-br', feature.color]"></div>

                        <div class="relative z-10">
                            <div class="flex items-center justify-between mb-8">
                                <span class="text-4xl p-4 bg-slate-50 dark:bg-slate-800/50 rounded-2xl">{{ feature.icon }}</span>
                                <span :class="[
                                    'px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest border',
                                    feature.status === 'Active' ? 'bg-emerald-500/10 text-emerald-500 border-emerald-500/20' : 'bg-amber-500/10 text-amber-500 border-amber-500/20'
                                ]">
                                    {{ feature.status }}
                                </span>
                            </div>
                            
                            <h3 class="text-2xl font-black text-slate-800 dark:text-white mb-3 group-hover:text-indigo-600 transition-colors">{{ feature.title }}</h3>
                            <p class="text-slate-500 dark:text-slate-400 font-medium leading-relaxed mb-8">{{ feature.description }}</p>

                            <Link 
                                :href="feature.link" 
                                class="inline-flex items-center text-sm font-black text-indigo-600 hover:text-indigo-700 uppercase tracking-widest gap-2 group/btn"
                            >
                                Explorer 
                                <svg class="w-4 h-4 transition-transform group-hover/btn:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                </svg>
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
