<script setup>
import { ref, computed } from 'vue';
import { Link, usePage, router } from '@inertiajs/vue3';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import { useTrans } from '@/Composables/useTrans';
import Swal from 'sweetalert2';

const { trans, setLocale, currentLocale } = useTrans();
const page = usePage();
const showingSidebar = ref(true);

const markAsRead = (id) => {
    router.post(route('notifications.markAsRead', id), {}, { preserveScroll: true });
};

const markAllAsRead = () => {
    router.post(route('notifications.markAllRead'), {}, { preserveScroll: true });
};

const navItems = [
    { name: 'nav_dashboard', route: 'dashboard', icon: 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6' },
    { name: 'nav_transports', route: 'transports.index', icon: 'M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0' },
    { name: 'nav_drivers', route: 'drivers.index', icon: 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z' },
    { name: 'nav_routes', route: 'routes.index', icon: 'M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-1.447-.894L15 7m0 10V7' },
    { name: 'nav_access', route: 'access.index', icon: 'M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z' },
    { name: 'nav_missions', route: 'driver.dashboard', icon: 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z' },
    { name: 'nav_history', route: 'driver.performance', icon: 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z' },
    { name: 'nav_ai', route: 'ai.hub', icon: 'M13 10V3L4 14h7v7l9-11h-7z' },
];

const filteredNavItems = computed(() => {
    const isDriver = !!page.props.auth.user?.has_driver;
    
    if (isDriver) {
        return navItems.filter(item => ['driver.dashboard', 'driver.performance'].includes(item.route));
    } else {
        return navItems.filter(item => !['driver.dashboard', 'driver.performance'].includes(item.route));
    }
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
            popup: 'rounded-3xl border-0 bg-white dark:bg-slate-900 text-slate-900 dark:text-white',
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
                    popup: 'rounded-3xl border-0 bg-white dark:bg-slate-900 text-slate-900 dark:text-white',
                }
            });
        }
    });
};
</script>

<template>
    <div class="app-layout font-sans text-slate-900 dark:text-slate-100">
        <!-- Sidebar -->
        <aside 
            :class="showingSidebar ? 'w-64' : 'w-20'"
            class="transition-all duration-300 ease-in-out bg-white dark:bg-slate-900 border-r border-slate-200 dark:border-slate-800 flex flex-col fixed inset-y-0 z-50 overflow-hidden"
        >
            <div class="h-16 flex items-center px-6 border-b border-slate-100 dark:border-slate-800 shrink-0">
                <div class="bg-gradient-to-br from-indigo-600 to-teal-500 p-1.5 rounded-lg mr-3 shadow-lg shadow-indigo-500/20">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                </div>
                <span v-if="showingSidebar" class="font-black text-xl tracking-tight">
                    <span class="bg-gradient-to-r from-indigo-600 to-teal-500 bg-clip-text text-transparent">RAGHD</span>
                    <span class="text-slate-400 font-medium text-sm ml-1">TMS</span>
                </span>
            </div>

            <nav class="flex-1 px-4 py-6 space-y-2 overflow-y-auto hidden-scrollbar">
                <template v-for="item in filteredNavItems" :key="item.name">
                    <Link 
                        :href="route(item.route)"
                        class="flex items-center px-3 py-2.5 rounded-xl transition-all duration-200 group relative overflow-hidden"
                        :class="route().current(item.route) ? 'bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 font-medium' : 'text-slate-500 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800/50 hover:text-slate-900 dark:hover:text-slate-200'"
                    >
                        <svg class="h-5 w-5 mr-3 shrink-0 transition-colors" :class="route().current(item.route) ? 'text-indigo-600' : 'text-slate-400 group-hover:text-slate-600'" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" :d="item.icon" />
                        </svg>
                        <span v-if="showingSidebar" class="truncate font-bold uppercase text-[11px] tracking-wider">{{ trans(item.name) }}</span>
                        
                        <!-- Active Indicator -->
                        <div v-if="route().current(item.route)" class="absolute left-0 top-0 bottom-0 w-1 bg-indigo-600 rounded-r-full"></div>
                    </Link>
                </template>
            </nav>

            <div class="px-4 py-4 space-y-2 border-t border-slate-100 dark:border-slate-800">
                <button 
                    @click="changeLanguage"
                    class="w-full flex items-center px-3 py-2.5 rounded-xl text-slate-500 hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-all group"
                >
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 11.37 9.198 15.287 6 17.5" />
                    </svg>
                    <span v-if="showingSidebar" class="text-[11px] font-black uppercase tracking-widest">{{ trans('swal_select_lang') }}</span>
                </button>

                <button 
                    @click="showingSidebar = !showingSidebar"
                    class="w-full flex items-center justify-center p-2 rounded-lg bg-slate-50 dark:bg-slate-800/50 text-slate-500 hover:text-indigo-600 transition-colors"
                >
                    <svg class="w-5 h-5 transition-transform duration-300" :class="!showingSidebar ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7" />
                    </svg>
                </button>
            </div>
        </aside>

        <!-- Main Content -->
        <div 
            :class="showingSidebar ? 'pl-64' : 'pl-20'" 
            class="main-content transition-all duration-300 ease-in-out"
        >
            <!-- Header -->
            <header class="h-16 sticky top-0 bg-white/80 dark:bg-slate-900/80 backdrop-blur-md border-b border-slate-200 dark:border-slate-800 z-40 flex items-center justify-between px-8 shadow-sm">
                <h1 class="font-bold text-lg text-slate-800 dark:text-slate-100 uppercase tracking-tight">
                    <slot name="header"></slot>
                </h1>

                <div class="flex items-center space-x-4">
                    <!-- Notifications -->
                    <div class="relative">
                        <Dropdown align="right" width="80" contentClasses="py-1 bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-2xl shadow-xl overflow-hidden min-w-[320px]">
                            <template #trigger>
                                <button class="p-2 text-slate-400 hover:text-indigo-600 transition-colors rounded-full hover:bg-slate-100 dark:hover:bg-slate-800 relative">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                                    </svg>
                                    <span v-if="$page.props.auth.notifications.length > 0" class="absolute top-1 right-1 flex min-w-3 min-h-3">
                                      <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-rose-400 opacity-75"></span>
                                      <span class="relative inline-flex flex-1 items-center justify-center rounded-full bg-rose-500 border border-white dark:border-slate-900 px-[4px] py-[2px] text-[8px] font-black leading-none text-white shadow-sm">{{ $page.props.auth.notifications.length }}</span>
                                    </span>
                                </button>
                            </template>
                            <template #content>
                                <div class="px-4 py-3 border-b border-slate-100 dark:border-slate-800 flex justify-between items-center bg-slate-50 dark:bg-slate-950">
                                    <h3 class="text-xs font-black text-slate-700 dark:text-slate-200 uppercase tracking-widest">Notifications</h3>
                                    <button v-if="$page.props.auth.notifications.length > 0" @click.stop="markAllAsRead" class="text-[9px] font-bold text-indigo-500 hover:text-indigo-600 uppercase tracking-widest transition-colors bg-indigo-50 dark:bg-indigo-500/10 px-2 py-1 rounded-md">Mark All</button>
                                </div>
                                <div v-if="$page.props.auth.notifications.length === 0" class="p-8 text-center flex flex-col items-center justify-center">
                                    <div class="w-12 h-12 rounded-full bg-slate-50 dark:bg-slate-800/50 flex items-center justify-center mb-3">
                                        <svg class="w-6 h-6 text-slate-300 dark:text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" /></svg>
                                    </div>
                                    <p class="text-xs font-bold text-slate-400 uppercase tracking-widest">Aucune notification</p>
                                </div>
                                <div v-else class="max-h-80 overflow-y-auto">
                                    <template v-for="notification in $page.props.auth.notifications" :key="notification.id">
                                        <div class="px-4 py-3.5 border-b border-slate-50 dark:border-slate-800/30 hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors group cursor-pointer flex gap-3 relative" @click="markAsRead(notification.id)">
                                            
                                            <!-- Colored dot indicator -->
                                            <div class="flex-shrink-0 mt-1 relative flex items-center justify-center">
                                                <template v-if="notification.data.type === 'success'">
                                                    <span class="absolute w-4 h-4 bg-emerald-500/20 rounded-full animate-ping"></span>
                                                    <span class="relative block w-2 h-2 rounded-full bg-emerald-500 shadow-[0_0_10px_rgba(16,185,129,0.8)]"></span>
                                                </template>
                                                <template v-else-if="notification.data.type === 'warning'">
                                                    <span class="absolute w-4 h-4 bg-amber-500/20 rounded-full animate-ping"></span>
                                                    <span class="relative block w-2 h-2 rounded-full bg-amber-500 shadow-[0_0_10px_rgba(245,158,11,0.8)]"></span>
                                                </template>
                                                <template v-else>
                                                    <span class="absolute w-4 h-4 bg-indigo-500/20 rounded-full animate-ping"></span>
                                                    <span class="relative block w-2 h-2 rounded-full bg-indigo-500 shadow-[0_0_10px_rgba(99,102,241,0.8)]"></span>
                                                </template>
                                            </div>
                                            
                                            <!-- Notification text -->
                                            <div class="w-0 flex-1">
                                                <p class="text-[11px] font-black text-slate-700 dark:text-slate-200 uppercase tracking-wider mb-0.5">{{ notification.data.title }}</p>
                                                <p class="text-[11px] text-slate-500 dark:text-slate-400 leading-relaxed">{{ notification.data.message }}</p>
                                                <p class="mt-2 text-[9px] font-bold text-slate-400 uppercase tracking-widest flex items-center">
                                                    <svg class="w-3 h-3 mr-1 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                                    il y a quelques instants
                                                </p>
                                            </div>

                                            <!-- Dismiss button (appears on hover) -->
                                            <button class="absolute top-3 right-3 p-1 rounded-full text-slate-300 hover:text-slate-500 dark:text-slate-600 dark:hover:text-slate-400 opacity-0 group-hover:opacity-100 transition-opacity bg-white dark:bg-slate-800 shadow-sm border border-slate-100 dark:border-slate-700" title="Marquer comme lu">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                            </button>
                                        </div>
                                    </template>
                                </div>
                            </template>
                        </Dropdown>
                    </div>

                    <!-- User Profile Dropdown -->
                    <div class="relative">
                        <Dropdown align="right" width="48">
                            <template #trigger>
                                <button class="flex items-center space-x-3 p-1 rounded-full hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors border border-transparent hover:border-slate-200 dark:hover:border-slate-700">
                                    <div class="w-8 h-8 rounded-full bg-indigo-600 flex items-center justify-center text-white font-black text-xs uppercase">
                                        {{ $page.props.auth.user.name.charAt(0) }}
                                    </div>
                                    <span class="hidden md:block text-[11px] font-black uppercase tracking-widest pr-2">{{ $page.props.auth.user.name }}</span>
                                </button>
                            </template>
                            <template #content>
                                <div class="px-4 py-3 border-b border-slate-100 dark:border-slate-800">
                                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ trans('signed_in_as') }}</p>
                                    <p class="text-sm font-bold truncate text-slate-700 dark:text-slate-200 mt-1">{{ $page.props.auth.user.email }}</p>
                                </div>
                                <DropdownLink :href="route('profile.edit')" class="text-xs font-bold uppercase tracking-wider py-3">{{ trans('profile') }}</DropdownLink>
                                <DropdownLink :href="route('logout')" method="post" as="button" class="text-xs font-bold uppercase tracking-wider py-3 text-rose-500">
                                    {{ trans('nav_logout') }}
                                </DropdownLink>
                            </template>
                        </Dropdown>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <main class="flex-1 p-8 bg-slate-50 dark:bg-slate-950">
                <div class="max-w-7xl mx-auto animate-in fade-in slide-in-from-bottom-4 duration-500">
                    <slot />
                </div>
            </main>
            
            <footer class="py-10 border-t border-slate-200 dark:border-slate-800 px-8 text-center text-slate-400 text-xs font-medium uppercase tracking-[0.2em]">
                &copy; {{ new Date().getFullYear() }} STN RAGHD TMS. {{ trans('footer_rights') }}
            </footer>
        </div>
    </div>
</template>

<style>
/* Robust Sidebar Scrollbar Hiding */
.hidden-scrollbar::-webkit-scrollbar {
    display: none !important;
}
.hidden-scrollbar {
    -ms-overflow-style: none !important;
    scrollbar-width: none !important;
}

/* THE PREMIUM INDIGO ELEVATOR (Consolidated to Root) */
:root {
    scrollbar-color: #4f46e5 transparent;
    scrollbar-width: thin;
}

::-webkit-scrollbar {
    width: 10px;
}

::-webkit-scrollbar-track {
    background: transparent;
}

::-webkit-scrollbar-thumb {
    background-color: #4f46e5; /* indigo-600 - More Vivid */
    border-radius: 20px;
    border: 2px solid transparent;
    background-clip: content-box;
}

::-webkit-scrollbar-thumb:hover {
    background-color: #3730a3; /* indigo-800 */
}

/* Layout Reset */
html, body {
    margin: 0;
    padding: 0;
    height: 100%;
    /* Ensure only one scrollbar ever exists */
    overflow-x: hidden;
    overflow-y: auto; 
}

/* Theme Awareness for White Space Fix */
html {
    background-color: #f8fafc;
}
html.dark {
    background-color: #020617;
}

#app {
    min-height: 100%;
}

/* Sticky Footer Implementation */
.app-layout {
    display: flex;
    flex-direction: row;
    min-height: 100vh;
}

.main-content {
    display: flex;
    flex-direction: column;
    flex: 1;
    min-height: 100vh;
    width: 100%;
}

/* Force main area to fill space and push footer down */
main {
    flex: 1;
}

/* Consistency checks for the .dark class */
.dark body, .dark .app-layout {
    background-color: #020617 !important;
}
</style>
