<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';
import { useTrans } from '@/Composables/useTrans';
import { Line } from 'vue-chartjs';
import {
    Chart as ChartJS,
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    Title,
    Tooltip,
    Legend
} from 'chart.js';

ChartJS.register(
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    Title,
    Tooltip,
    Legend
);

const { trans } = useTrans();

const props = defineProps({
    missions: Array,
    driver: Object,
    stats: Object
});

const chartData = computed(() => ({
    labels: props.stats.history.map(h => h.date),
    datasets: [
        {
            label: trans('driver_points'),
            backgroundColor: '#4f46e5',
            borderColor: '#4f46e5',
            data: props.stats.history.map(h => h.points),
            tension: 0.4,
            fill: false,
            pointBackgroundColor: '#4f46e5',
            pointBorderColor: '#fff',
            pointBorderWidth: 2,
            pointRadius: 6,
        }
    ]
}));

const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: { display: false },
        tooltip: {
            backgroundColor: '#1e293b',
            titleFont: { size: 14, weight: 'bold' },
            padding: 12,
            cornerRadius: 12,
        }
    },
    scales: {
        y: { 
            beginAtZero: true,
            grid: { color: 'rgba(0,0,0,0.05)' },
            ticks: { font: { weight: 'bold' } }
        },
        x: { 
            grid: { display: false },
            ticks: { font: { weight: 'bold' } }
        }
    }
};
</script>

<template>
    <Head :title="trans('nav_history')" />

    <AuthenticatedLayout>
        <template #header>
            {{ trans('nav_history') }}: {{ driver.first_name }}
        </template>

        <div class="space-y-10 pb-20">
            <!-- Stats Overview -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="bg-white dark:bg-slate-900 p-8 rounded-[2.5rem] shadow-xl border border-slate-100 dark:border-slate-800 flex flex-col justify-between group">
                    <div>
                        <div class="w-12 h-12 bg-indigo-50 dark:bg-indigo-900/30 rounded-2xl flex items-center justify-center text-indigo-600 mb-6 font-black uppercase text-xs">P</div>
                        <h4 class="text-slate-400 font-black uppercase tracking-widest text-[10px]">{{ trans('driver_points') }}</h4>
                    </div>
                    <div class="flex items-end justify-between mt-4">
                        <span class="text-5xl font-black text-indigo-600 tracking-tighter">{{ stats.points }}</span>
                    </div>
                </div>

                <div class="bg-white dark:bg-slate-900 p-8 rounded-[2.5rem] shadow-xl border border-slate-100 dark:border-slate-800 flex flex-col justify-between group">
                    <div>
                        <div class="w-12 h-12 bg-emerald-50 dark:bg-emerald-900/30 rounded-2xl flex items-center justify-center text-emerald-600 mb-6 font-black uppercase text-xs">S</div>
                        <h4 class="text-slate-400 font-black uppercase tracking-widest text-[10px]">{{ trans('driver_success_rate') }}</h4>
                    </div>
                    <div class="flex items-end justify-between mt-4">
                        <span class="text-5xl font-black text-emerald-600 tracking-tighter">{{ stats.success_rate }}%</span>
                    </div>
                </div>

                <div class="bg-white dark:bg-slate-900 p-8 rounded-[2.5rem] shadow-xl border border-slate-100 dark:border-slate-800 flex flex-col justify-between group">
                    <div>
                        <div class="w-12 h-12 bg-sky-50 dark:bg-sky-900/30 rounded-2xl flex items-center justify-center text-sky-600 mb-6 font-black uppercase text-xs">M</div>
                        <h4 class="text-slate-400 font-black uppercase tracking-widest text-[10px]">{{ trans('driver_total_missions') }}</h4>
                    </div>
                    <div class="flex items-end justify-between mt-4">
                        <span class="text-5xl font-black text-sky-600 tracking-tighter">{{ stats.total }}</span>
                    </div>
                </div>
            </div>

            <!-- Performance Chart -->
            <div class="bg-white dark:bg-slate-900 p-10 rounded-[3rem] shadow-2xl border border-slate-100 dark:border-slate-800">
                <h3 class="text-2xl font-black tracking-tighter uppercase mb-10">{{ trans('driver_chart_title') }}</h3>
                <div class="h-[300px] relative">
                    <Line :data="chartData" :options="chartOptions" />
                </div>
            </div>

            <!-- History Section -->
            <div class="bg-white dark:bg-slate-900 rounded-[3rem] shadow-xl border border-slate-100 dark:border-slate-800 overflow-hidden">
                <div class="p-10 border-b border-slate-100 dark:border-slate-800 font-black uppercase tracking-tighter text-2xl">
                    {{ trans('driver_history_title') }}
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead class="bg-slate-50 dark:bg-slate-800/50">
                            <tr>
                                <th class="px-10 py-5 text-[10px] font-black uppercase tracking-widest text-slate-400">{{ trans('mission_id') }}</th>
                                <th class="px-10 py-5 text-[10px] font-black uppercase tracking-widest text-slate-400">Route</th>
                                <th class="px-10 py-5 text-[10px] font-black uppercase tracking-widest text-slate-400">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            <tr v-for="m in missions" :key="m.id" class="hover:bg-slate-50/50 dark:hover:bg-slate-800/30 transition-colors">
                                <td class="px-10 py-6 font-mono text-sm">#{{ m.id.toString().padStart(5, '0') }}</td>
                                <td class="px-10 py-6 text-sm font-black uppercase">{{ m.origin }} → {{ m.destination }}</td>
                                <td class="px-10 py-6">
                                    <span 
                                        class="text-[10px] font-black uppercase tracking-widest px-3 py-1 rounded-full"
                                        :class="{
                                            'bg-emerald-50 text-emerald-600': m.status === 'completed',
                                            'bg-rose-50 text-rose-600': m.status === 'failed',
                                            'bg-indigo-50 text-indigo-600': m.status === 'ongoing'
                                        }"
                                    >
                                        {{ trans('status_' + m.status) }}
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
