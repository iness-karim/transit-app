<script setup>
import Checkbox from '@/Components/Checkbox.vue';
import InputError from '@/Components/InputError.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { useTrans } from '@/Composables/useTrans';

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const { trans } = useTrans();
const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <div class="min-h-screen bg-slate-50 flex items-center justify-center p-6 font-sans">
        <Head title="Login - RAGHD TMS" />

        <div class="w-full max-w-md">
            <!-- Logo Section -->
            <div class="text-center mb-10">
                <div class="inline-flex bg-gradient-to-br from-indigo-600 to-teal-500 p-3 rounded-2xl shadow-xl shadow-indigo-500/20 mb-4 animate-in zoom-in-0 duration-500">
                    <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                </div>
                <h1 class="text-3xl font-black tracking-tighter text-slate-900">
                    <span class="bg-gradient-to-r from-indigo-600 to-teal-500 bg-clip-text text-transparent">RAGHD</span>
                    <span class="text-slate-400 font-medium text-xl ml-1">TMS</span>
                </h1>
                <p class="text-slate-500 font-medium mt-1 uppercase text-[10px] tracking-[0.3em] ml-1">{{ trans('login_subtitle') }}</p>
            </div>

            <!-- Card -->
            <div class="bg-white rounded-[2.5rem] p-8 md:p-10 shadow-2xl shadow-slate-200/50 border border-slate-100">
                <h2 class="text-xl font-bold text-slate-900 mb-8">{{ trans('login_title') }}</h2>

                <div v-if="status" class="mb-6 bg-emerald-50 text-emerald-600 p-4 rounded-2xl text-sm font-bold border border-emerald-100">
                    {{ status }}
                </div>

                <form @submit.prevent="submit" class="space-y-6">
                    <div class="space-y-2">
                        <label class="text-xs font-black uppercase tracking-widest text-slate-400 ml-1">{{ trans('login_email') }}</label>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400 group-focus-within:text-indigo-600 transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <input 
                                v-model="form.email" 
                                type="email" 
                                required 
                                autofocus 
                                autocomplete="username"
                                placeholder="name@company.com"
                                class="w-full bg-slate-50 border-slate-100 rounded-2xl py-4 pl-12 pr-4 focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all font-medium text-slate-900 placeholder:text-slate-300"
                            >
                        </div>
                        <InputError :message="form.errors.email" />
                    </div>

                    <div class="space-y-2">
                        <div class="flex justify-between items-center ml-1">
                            <label class="text-xs font-black uppercase tracking-widest text-slate-400">{{ trans('login_password') }}</label>
                            <Link v-if="canResetPassword" :href="route('password.request')" class="text-[10px] font-black uppercase tracking-widest text-indigo-600 hover:text-indigo-700">Lost Key?</Link>
                        </div>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400 group-focus-within:text-indigo-600 transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                </svg>
                            </div>
                            <input 
                                v-model="form.password" 
                                type="password" 
                                required 
                                autocomplete="current-password"
                                placeholder="••••••••"
                                class="w-full bg-slate-50 border-slate-100 rounded-2xl py-4 pl-12 pr-4 focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all font-medium text-slate-900 placeholder:text-slate-300"
                            >
                        </div>
                        <InputError :message="form.errors.password" />
                    </div>

                    <div class="flex items-center justify-between">
                        <label class="relative flex items-center cursor-pointer group">
                            <input type="checkbox" v-model="form.remember" class="sr-only peer">
                            <div class="w-10 h-6 bg-slate-100 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-4 peer-checked:after:bg-white after:content-[''] after:absolute after:top-[4px] after:left-[4px] after:bg-slate-300 after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-indigo-600"></div>
                            <span class="ml-3 text-xs font-black uppercase tracking-widest text-slate-400 group-hover:text-slate-600 transition-colors">{{ trans('login_remember') }}</span>
                        </label>
                    </div>

                    <div class="pt-4">
                        <button 
                            type="submit" 
                            :disabled="form.processing"
                            class="w-full bg-indigo-600 text-white py-4 rounded-2xl font-black text-sm uppercase tracking-[0.2em] hover:bg-indigo-700 focus:ring-4 focus:ring-indigo-500/30 transition-all shadow-xl shadow-indigo-600/20 disabled:opacity-50"
                        >
                            <span v-if="!form.processing">{{ trans('login_btn') }}</span>
                            <span v-else class="flex items-center justify-center">
                                <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </span>
                        </button>
                    </div>
                </form>

                <div class="mt-10 text-center">
                    <p class="text-xs font-bold text-slate-400">
                        {{ trans('new_operator') }} 
                        <Link :href="route('register')" class="text-indigo-600 hover:underline ml-1">{{ trans('req_credentials') }}</Link>
                    </p>
                </div>
            </div>

            <!-- Footer Meta -->
            <div class="mt-12 text-center text-[10px] font-black uppercase tracking-[0.3em] text-slate-300">
                &copy; {{ new Date().getFullYear() }} STN RAGHD TMS &mdash; Secure Access Node
            </div>
        </div>
    </div>
</template>

<style scoped>
/* Custom toggle button focus/hover effects if needed */
</style>
