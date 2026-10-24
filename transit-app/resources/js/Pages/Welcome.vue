<script setup>
import { Head, Link } from '@inertiajs/vue3';
import ChatBot from '@/Components/ChatBot.vue';
import { useTrans } from '@/Composables/useTrans';
import Swal from 'sweetalert2';

const { trans, setLocale, currentLocale } = useTrans();

defineProps({
    canLogin: Boolean,
    canRegister: Boolean,
});

const changeLanguage = () => {
    Swal.fire({
        title: trans('swal_select_lang'),
        input: 'radio',
        inputOptions: {
            'en': trans('swal_en'),
            'fr': trans('swal_fr')
        },
        inputValue: currentLocale.value,
        confirmButtonText: 'OK',
        customClass: {
            popup: 'rounded-3xl border-0 bg-white text-slate-900',
            title: 'font-bold text-2xl',
            confirmButton: 'bg-indigo-600 rounded-2xl px-8 py-3 font-bold',
        }
    }).then((result) => {
        if (result.value) {
            setLocale(result.value);
            Swal.fire({
                title: trans('swal_success'),
                icon: 'success',
                timer: 1500,
                showConfirmButton: false,
                customClass: {
                    popup: 'rounded-3xl border-0 bg-white text-slate-900',
                }
            });
        }
    });
};
</script>

<template>
    <Head title="RAGHD TMS - STN RAGHD Transportation Management System" />
    
    <div class="min-h-screen bg-slate-50 text-slate-900 font-sans selection:bg-indigo-600 selection:text-white">
        <!-- Navigation -->
        <nav class="sticky top-0 z-50 bg-white/70 backdrop-blur-xl border-b border-slate-200">
            <div class="max-w-7xl mx-auto px-6 sm:px-8 flex items-center justify-between h-20">
                <div class="flex items-center space-x-3 group cursor-pointer">
                    <div class="bg-gradient-to-br from-indigo-600 to-teal-500 p-2.5 rounded-2xl shadow-lg shadow-indigo-200 group-hover:scale-110 transition-transform">
                        <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                    </div>
                    <span class="text-2xl font-black tracking-tighter text-slate-900">
                        <span class="bg-gradient-to-r from-indigo-600 to-teal-500 bg-clip-text text-transparent">RAGHD</span>
                        <span class="text-slate-400 font-medium text-base ml-1">TMS</span>
                    </span>
                </div>
                
                <div v-if="canLogin" class="flex items-center space-x-4 sm:space-x-8">
                    <button @click="changeLanguage" class="flex items-center space-x-2 text-slate-600 hover:text-indigo-600 font-bold transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 11.37 9.198 15.287 6 17.5" />
                        </svg>
                        <span class="uppercase tracking-widest text-[10px]">{{ currentLocale }}</span>
                    </button>
                    <template v-if="$page.props.auth.user">
                        <Link :href="route('dashboard')" class="font-bold text-white bg-indigo-600 px-6 py-3 rounded-2xl hover:bg-indigo-700 transition-all shadow-xl shadow-indigo-100">{{ trans('nav_go_dashboard') }}</Link>
                    </template>
                    <template v-else>
                        <Link :href="route('login')" class="font-bold text-slate-600 hover:text-indigo-600 transition-colors">{{ trans('nav_login') }}</Link>
                    </template>
                </div>
            </div>
        </nav>

        <!-- Hero Section -->
        <main class="relative overflow-hidden pt-16 pb-24 sm:pt-24 sm:pb-32">
            <!-- Background Elements -->
            <div class="absolute top-0 left-1/2 -translate-x-1/2 w-full max-w-7xl h-full -z-10 pointer-events-none">
                <div class="absolute top-20 right-0 w-[500px] h-[500px] bg-indigo-100/50 blur-[100px] rounded-full"></div>
                <div class="absolute bottom-0 left-0 w-[400px] h-[400px] bg-sky-100/50 blur-[80px] rounded-full"></div>
            </div>

            <div class="max-w-7xl mx-auto px-6 sm:px-8 lg:grid lg:grid-cols-2 lg:gap-16 items-center">
                <div class="text-center lg:text-left space-y-10 focus:outline-none">
                    <div class="inline-flex items-center space-x-3 bg-white border border-slate-200 px-4 py-2 rounded-full shadow-sm">
                        <span class="flex h-2 w-2 rounded-full bg-teal-500 animate-pulse"></span>
                        <span class="text-xs font-black uppercase tracking-widest text-slate-500">STN RAGHD Transportation Management</span>
                    </div>

                    <h1 class="text-6xl sm:text-7xl lg:text-8xl font-black text-slate-900 leading-[0.95] tracking-tighter">
                        <template v-if="currentLocale === 'en'">
                            Move <span class="text-indigo-600 italic">Anything,</span><br>Anywhere.
                        </template>
                        <template v-else>
                            Déplacez <span class="text-indigo-600 italic">Tout,</span><br>Partout.
                        </template>
                    </h1>

                    <p class="text-xl text-slate-500 max-w-xl mx-auto lg:mx-0 leading-relaxed font-medium">
                        {{ trans('hero_subtitle') }}
                    </p>

                    <div class="pt-8 flex flex-col items-center lg:items-start space-y-4">
                        <p class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">{{ trans('trusted_by') }}</p>
                        <div class="flex items-center space-x-8 opacity-40 grayscale">
                            <!-- Placeholder Logos -->
                            <div class="font-black text-xl italic tracking-tighter hover:text-indigo-600 transition-colors cursor-default">FAST-CO</div>
                            <div class="font-black text-xl italic tracking-tighter hover:text-indigo-600 transition-colors cursor-default">GLOBAL-X</div>
                            <div class="font-black text-xl italic tracking-tighter hover:text-indigo-600 transition-colors cursor-default">TRANS-IT</div>
                        </div>
                    </div>
                </div>

                <div class="mt-20 lg:mt-0 relative">
                    <div class="bg-white rounded-[3rem] shadow-[0_40px_100px_-20px_rgba(79,70,229,0.15)] border border-slate-100 p-6 sm:p-10 relative z-10 overflow-hidden group hover:scale-[1.02] transition-transform duration-500">
                        <!-- Image Container -->
                        <div class="aspect-square bg-slate-50 rounded-[2rem] border border-slate-100 overflow-hidden relative">
                             <img src="/images/logistics_hero_v2.png" alt="Logistics Hero" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700" />
                             
                             <!-- Overlay Gradient -->
                             <div class="absolute inset-0 bg-gradient-to-t from-indigo-900/20 to-transparent"></div>

                             <!-- Floating Cards -->
                             <div class="absolute top-8 right-8 bg-white p-4 rounded-2xl shadow-xl border border-slate-100 flex items-center space-x-3 hover:-translate-y-1 transition-transform">
                                <div class="w-10 h-10 bg-emerald-100 rounded-xl flex items-center justify-center text-emerald-600 font-bold">✓</div>
                                <div>
                                    <p class="text-[10px] text-slate-400 font-bold uppercase">{{ trans('delivery_status') }}</p>
                                    <p class="text-sm font-black text-slate-800">{{ trans('on_time') }}</p>
                                </div>
                             </div>
                             
                             <div class="absolute bottom-8 left-8 bg-white p-4 rounded-2xl shadow-xl border border-slate-100 flex items-center space-x-3 hover:translate-y-1 transition-transform">
                                <div class="w-10 h-10 bg-indigo-600 rounded-xl flex items-center justify-center text-white font-bold">↑</div>
                                <div>
                                    <p class="text-[10px] text-slate-400 font-bold uppercase">{{ trans('efficiency') }}</p>
                                    <p class="text-sm font-black text-slate-800">+12.4%</p>
                                </div>
                             </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>

        <!-- New Section: Fleet of Tomorrow -->
        <section class="py-32 bg-slate-50 relative overflow-hidden">
            <!-- Decorative Floating Elements -->
            <div class="absolute top-20 right-10 w-4 h-4 bg-indigo-400 rounded-full animate-float opacity-30"></div>
            <div class="absolute bottom-20 left-10 w-6 h-6 bg-sky-400 rounded-full animate-float [animation-delay:2s] opacity-30"></div>
            <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[800px] h-[800px] bg-white/50 blur-[150px] rounded-full pointer-events-none -z-10"></div>
            
            <div class="absolute -right-20 top-40 w-96 h-96 bg-indigo-100/40 blur-[120px] rounded-full pointer-events-none"></div>
            
            <div class="max-w-7xl mx-auto px-6 sm:px-8">
                <div class="text-center mb-24 space-y-6">
                    <h2 class="text-4xl sm:text-6xl font-black text-slate-900 tracking-tighter">{{ trans('fleet_of_tomorrow') }}</h2>
                    <p class="text-lg text-slate-500 max-w-2xl mx-auto font-medium leading-relaxed">
                        {{ trans('fleet_subtitle') }}
                    </p>
                </div>

                <div class="grid lg:grid-cols-2 gap-12 items-center">
                    <!-- Tech Feature 1 -->
                    <div class="bg-white p-8 rounded-[3rem] border border-slate-100 shadow-2xl shadow-indigo-100/20 flex flex-col sm:flex-row items-center gap-8 group hover:-translate-y-2 transition-all duration-500">
                        <div class="w-full sm:w-1/2 aspect-video bg-indigo-50 rounded-[2rem] overflow-hidden relative">
                            <img src="/images/global_network.png" alt="Global Network" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700" />
                            <div class="absolute inset-0 bg-indigo-600/10 mix-blend-overlay"></div>
                        </div>
                        <div class="w-full sm:w-1/2 space-y-4 text-center sm:text-left">
                            <h3 class="text-2xl font-black text-slate-900">{{ trans('neural_network_routing_title') }}</h3>
                            <p class="text-slate-500 text-sm font-medium leading-relaxed">
                                {{ trans('neural_network_routing_description') }}
                            </p>
                        </div>
                    </div>

                    <!-- Tech Feature 2 -->
                    <div class="bg-white p-8 rounded-[3rem] border border-slate-100 shadow-2xl shadow-sky-100/20 flex flex-col sm:flex-row items-center gap-8 group hover:-translate-y-2 transition-all duration-500">
                        <div class="w-full sm:w-1/2 aspect-video bg-sky-50 rounded-[2rem] overflow-hidden relative">
                            <img src="/images/drone_delivery.png" alt="Drone Delivery" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700" />
                            <div class="absolute inset-0 bg-sky-600/10 mix-blend-overlay"></div>
                        </div>
                        <div class="w-full sm:w-1/2 space-y-4 text-center sm:text-left">
                            <h3 class="text-2xl font-black text-slate-900">{{ trans('adaptive_fleet_sync_title') }}</h3>
                            <p class="text-slate-500 text-sm font-medium leading-relaxed">
                                {{ trans('adaptive_fleet_sync_description') }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Features Grid -->
        <section class="py-24 bg-white">
            <div class="max-w-7xl mx-auto px-6 sm:px-8 text-center sm:text-left">
                <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-12">
                    <div class="space-y-6 group">
                        <div class="w-16 h-16 bg-indigo-50 rounded-[1.5rem] flex items-center justify-center text-indigo-600 transition-colors group-hover:bg-indigo-600 group-hover:text-white">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                            </svg>
                        </div>
                        <h3 class="text-2xl font-black text-slate-900">{{ trans('precision_routing_title') }}</h3>
                        <p class="text-slate-500 leading-relaxed font-medium">
                            {{ trans('precision_routing_description') }}
                        </p>
                    </div>

                    <div class="space-y-6 group">
                        <div class="w-16 h-16 bg-sky-50 rounded-[1.5rem] flex items-center justify-center text-sky-600 transition-colors group-hover:bg-sky-600 group-hover:text-white">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 15.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                        </div>
                        <h3 class="text-2xl font-black text-slate-900">{{ trans('elite_fleet_title') }}</h3>
                        <p class="text-slate-500 leading-relaxed font-medium">
                            {{ trans('elite_fleet_description') }}
                        </p>
                    </div>

                    <div class="space-y-6 group">
                        <div class="w-16 h-16 bg-emerald-50 rounded-[1.5rem] flex items-center justify-center text-emerald-600 transition-colors group-hover:bg-emerald-600 group-hover:text-white">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                        </div>
                        <h3 class="text-2xl font-black text-slate-900">{{ trans('realtime_pulse_title') }}</h3>
                        <p class="text-slate-500 leading-relaxed font-medium">
                            {{ trans('realtime_pulse_description') }}
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="bg-slate-950 text-white pt-24 pb-12 overflow-hidden relative">
            <div class="absolute top-0 right-0 w-[600px] h-[600px] bg-indigo-600/10 blur-[120px] rounded-full translate-x-1/2 -translate-y-1/2 pointer-events-none"></div>
            
            <div class="max-w-7xl mx-auto px-6 sm:px-8">
                <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-12 pb-20 border-b border-white/10">
                    <div class="space-y-8">
                        <div class="flex items-center space-x-3">
                            <div class="bg-gradient-to-br from-indigo-600 to-teal-500 p-2 rounded-xl">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                </svg>
                            </div>
                            <span class="text-xl font-black tracking-tighter">
                                <span class="bg-gradient-to-r from-indigo-400 to-teal-400 bg-clip-text text-transparent">RAGHD</span>
                                <span class="text-slate-500 font-medium text-sm ml-1">TMS</span>
                            </span>
                        </div>
                        <p class="text-slate-400 text-sm leading-relaxed max-w-xs font-medium">
                            {{ trans('footer_tagline') }}
                        </p>
                    </div>

                    <div class="space-y-6">
                        <h4 class="text-xs font-black uppercase tracking-[0.2em] text-indigo-400">{{ trans('company_title') }}</h4>
                        <ul class="space-y-4 text-slate-400 font-medium text-sm">
                            <li class="hover:text-white transition-colors cursor-pointer">{{ trans('about_us') }}</li>
                            <li class="hover:text-white transition-colors cursor-pointer">{{ trans('careers') }}</li>
                            <li class="hover:text-white transition-colors cursor-pointer">{{ trans('newsroom') }}</li>
                        </ul>
                    </div>

                    <div class="space-y-6">
                        <h4 class="text-xs font-black uppercase tracking-[0.2em] text-indigo-400">{{ trans('services_title') }}</h4>
                        <ul class="space-y-4 text-slate-400 font-medium text-sm">
                            <li class="hover:text-white transition-colors cursor-pointer">{{ trans('freight_tracking') }}</li>
                            <li class="hover:text-white transition-colors cursor-pointer">{{ trans('fleet_manager') }}</li>
                            <li class="hover:text-white transition-colors cursor-pointer">{{ trans('security_center') }}</li>
                        </ul>
                    </div>

                    <div class="space-y-6">
                        <h4 class="text-xs font-black uppercase tracking-[0.2em] text-indigo-400">{{ trans('contact_title') }}</h4>
                        <ul class="space-y-4 text-slate-400 font-medium text-sm">
                            <li class="hover:text-white transition-colors cursor-pointer">{{ trans('support_hub') }}</li>
                            <li class="hover:text-white transition-colors cursor-pointer">{{ trans('enterprise_sales') }}</li>
                            <li class="hover:text-white transition-colors cursor-pointer">{{ trans('documentation') }}</li>
                        </ul>
                    </div>
                </div>

                <div class="pt-12 flex flex-col sm:flex-row justify-between items-center text-slate-500 text-[10px] font-black tracking-widest space-y-4 sm:space-y-0 uppercase">
                    <p>&copy; {{ new Date().getFullYear() }} STN RAGHD TRANSPORTATION MANAGEMENT SYSTEM. {{ trans('footer_rights') }}</p>
                    <div class="flex space-x-8">
                        <span class="hover:text-white cursor-pointer transition-colors">{{ trans('privacy_policy') }}</span>
                        <span class="hover:text-white cursor-pointer transition-colors">{{ trans('terms_of_service') }}</span>
                    </div>
                </div>
            </div>
        </footer>

        <!-- ChatBot Component -->
        <ChatBot />
    </div>
</template>

<style>
@import url('https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800;900&display=swap');

.font-sans {
    font-family: 'Outfit', sans-serif;
}

@keyframes float {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-20px); }
}

.animate-float {
    animation: float 6s ease-in-out infinite;
}
</style>
