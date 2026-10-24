<template>
  <div style="height: 400px; width: 100%;" class="rounded-xl overflow-hidden shadow-sm border border-slate-200 dark:border-slate-800 relative z-10 font-sans">
    <l-map ref="map" v-model:zoom="zoom" :center="center" :use-global-leaflet="false">
      <!-- Minimalist Dark/Light theme tiles (simulated with standard openstreetmap for now, optionally styled by css filters) -->
      <l-tile-layer
        url="https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png"
        layer-type="base"
        name="OpenStreetMap"
        class="map-tiles"
      ></l-tile-layer>

      <!-- Draw nodes for active terminal hubs -->
      <l-circle-marker 
        v-for="location in mappedLocations" 
        :key="location.name" 
        :lat-lng="location.coords" 
        :radius="8" 
        color="#ffffff" 
        fillColor="#3b82f6" 
        :fillOpacity="1" 
        :weight="3">
        <l-tooltip :options="{ permanent: true, direction: 'top', className: 'modern-tooltip' }">
            {{ location.name }}
        </l-tooltip>
      </l-circle-marker>
      
      <!-- Polyline logic for in progress/pending routes -->
      <l-polyline :lat-lngs="activeLines" color="#4f46e5" :weight="4" :opacity="0.8" className="route-line-anim"></l-polyline>
    </l-map>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import 'leaflet/dist/leaflet.css';
import { LMap, LTileLayer, LCircleMarker, LTooltip, LPolyline } from '@vue-leaflet/vue-leaflet';

const props = defineProps({
  routes: {
      type: Array,
      default: () => []
  }
});

const zoom = ref(5.5);
const center = ref([31.7917, -7.0926]); // Center of Morocco

// Hardcoded coordinate map for RAGHD operations
const cityCoords = {
    'Casablanca': [33.5731, -7.5898],
    'Rabat': [34.0209, -6.8416],
    'Marrakech': [31.6295, -7.9811],
    'Tangier': [35.7595, -5.8340],
    'Agadir': [30.4278, -9.5981],
    'Fes': [34.0331, -5.0003],
    'Oujda': [34.6814, -1.9086],
    'Kenitra': [34.2541, -6.5890],
    'Meknes': [33.8935, -5.5473],
    'Tetouan': [35.5785, -5.3684]
};

const mappedLocations = computed(() => {
    const locNames = new Set();
    const locs = [];
    
    props.routes.forEach(route => {
        // Only show cities that are part of active or pending operations
        if (route.status === 'pending' || route.status === 'in_progress') {
            if (cityCoords[route.start_location] && !locNames.has(route.start_location)) {
                locNames.add(route.start_location);
                locs.push({ name: route.start_location, coords: cityCoords[route.start_location] });
            }
            if (cityCoords[route.end_location] && !locNames.has(route.end_location)) {
                locNames.add(route.end_location);
                locs.push({ name: route.end_location, coords: cityCoords[route.end_location] });
            }
        }
    });

    // Fallback if no routes: show major hubs
    if (locs.length === 0) {
        locs.push({ name: 'Casablanca', coords: cityCoords['Casablanca'] });
        locs.push({ name: 'Tangier', coords: cityCoords['Tangier'] });
    }

    return locs;
});

const activeLines = computed(() => {
    return props.routes
        .filter(r => r.status === 'pending' || r.status === 'in_progress')
        .map(r => {
            const start = cityCoords[r.start_location];
            const end = cityCoords[r.end_location];
            if (start && end) return [start, end];
            return null;
        })
        .filter(pts => pts !== null);
});
</script>

<style>
/* Adjust map styles for dark mode and premium look */
.dark .map-tiles {
    filter: invert(100%) hue-rotate(180deg) brightness(95%) contrast(90%);
}

.modern-tooltip {
    background-color: #1e293b !important;
    color: white !important;
    border: none !important;
    border-radius: 8px !important;
    font-weight: 800 !important;
    text-transform: uppercase !important;
    letter-spacing: 0.1em !important;
    font-size: 9px !important;
    padding: 4px 8px !important;
    box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1) !important;
}
.modern-tooltip::before {
    border-top-color: #1e293b !important;
}

/* Subtle dash animation for in-transit feeling */
.route-line-anim {
    stroke-dasharray: 10, 10;
    animation: dash 20s linear infinite;
}

@keyframes dash {
  to {
    stroke-dashoffset: -1000;
  }
}
</style>
