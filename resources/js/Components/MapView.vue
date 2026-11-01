<script setup>
import { onMounted, onUnmounted, ref, watch } from 'vue';
import L from 'leaflet';
import { getCategoryEmoji } from '../utils/constants';
import { Crosshair, MapPin, Navigation, Plus, RotateCcw } from 'lucide-vue-next';

const props = defineProps({
    spots: {
        type: Array,
        required: true,
    },
    selectedSpot: {
        type: Object,
        default: null,
    },
    userLocation: {
        type: Object,
        default: null,
    },
    isAddingSpot: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['select-spot', 'map-click', 'locate-user']);

const mapContainer = ref(null);
let map = null;
let markersLayer = null;
let userMarker = null;
let tempPinMarker = null;

// Initial Dhaka Center Coordinates
const DHAKA_CENTER = [23.777176, 90.399452];
const DEFAULT_ZOOM = 12;

function createCustomMarkerIcon(spot, isSelected = false) {
    const emoji = getCategoryEmoji(spot.category);
    const isVerifiedHype = spot.trust_score >= 10;
    const isOverhypedScam = spot.scam_votes > spot.hype_votes;

    let borderClass = 'border-slate-700 bg-slate-900/95 text-slate-100 shadow-md';
    let badgeHtml = '';

    if (isVerifiedHype) {
        borderClass = 'border-emerald-400 bg-slate-900 text-white glow-emerald ring-2 ring-emerald-500/50';
        badgeHtml = `<span class="absolute -top-1.5 -right-1.5 w-4 h-4 bg-emerald-500 text-slate-950 font-black text-[9px] flex items-center justify-center rounded-full border border-slate-950 shadow">🔥</span>`;
    } else if (isOverhypedScam) {
        borderClass = 'border-rose-500 bg-slate-900 text-white glow-rose ring-2 ring-rose-500/50';
        badgeHtml = `<span class="absolute -top-1.5 -right-1.5 w-4 h-4 bg-rose-500 text-white font-black text-[9px] flex items-center justify-center rounded-full border border-slate-950 shadow">🤡</span>`;
    }

    const selectedClass = isSelected ? 'ring-4 ring-amber-400 scale-125 z-50' : '';

    const html = `
        <div class="custom-emoji-marker ${isSelected ? 'selected' : ''}">
            <div class="relative w-10 h-10 rounded-2xl flex items-center justify-center text-xl border-2 transition-all duration-300 ${borderClass} ${selectedClass}">
                <span class="transform transition-transform">${emoji}</span>
                ${badgeHtml}
            </div>
            <div class="w-2 h-1.5 bg-slate-800 rounded-full mx-auto -mt-0.5 opacity-80"></div>
        </div>
    `;

    return L.divIcon({
        className: 'custom-leaflet-div-icon',
        html: html,
        iconSize: [40, 44],
        iconAnchor: [20, 42],
        popupAnchor: [0, -42],
    });
}

function renderMarkers() {
    if (!map || !markersLayer) return;

    markersLayer.clearLayers();

    props.spots.forEach((spot) => {
        if (!spot.latitude || !spot.longitude) return;

        const isSelected = props.selectedSpot && props.selectedSpot.id === spot.id;
        const icon = createCustomMarkerIcon(spot, isSelected);

        const marker = L.marker([spot.latitude, spot.longitude], {
            icon: icon,
            title: `${spot.dish_name} - ${spot.shop_name}`,
            riseOnHover: true,
        });

        // Hover tooltip
        marker.bindTooltip(`
            <div class="p-1.5 bg-slate-900 text-slate-100 rounded-lg border border-slate-700 shadow-xl text-xs max-w-xs">
                <div class="font-bold text-emerald-400">${spot.dish_name}</div>
                <div class="text-slate-300 font-medium text-[11px]">${spot.shop_name} • ৳${spot.price}</div>
                <div class="text-slate-400 text-[10px] flex items-center gap-1 mt-0.5">
                    <span>${spot.area_name}</span>
                    <span class="text-slate-600">•</span>
                    <span class="${spot.trust_score >= 10 ? 'text-emerald-400 font-bold' : (spot.scam_votes > spot.hype_votes ? 'text-rose-400 font-bold' : 'text-amber-400')}">
                        ট্রাস্ট স্কোর: ${spot.trust_score}
                    </span>
                </div>
            </div>
        `, {
            direction: 'top',
            offset: [0, -40],
            opacity: 1,
            className: 'custom-dark-tooltip',
        });

        marker.on('click', () => {
            emit('select-spot', spot);
            map.flyTo([spot.latitude, spot.longitude], Math.max(map.getZoom(), 14), {
                duration: 0.8,
            });
        });

        markersLayer.addLayer(marker);
    });
}

function renderUserLocation() {
    if (!map) return;

    if (userMarker) {
        map.removeLayer(userMarker);
        userMarker = null;
    }

    if (props.userLocation && props.userLocation.lat && props.userLocation.lng) {
        const icon = L.divIcon({
            className: 'user-location-div-icon',
            html: `<div class="user-location-pulse" title="আপনার বর্তমান অবস্থান"></div>`,
            iconSize: [20, 20],
            iconAnchor: [10, 10],
        });

        userMarker = L.marker([props.userLocation.lat, props.userLocation.lng], {
            icon: icon,
            zIndexOffset: 1000,
        }).addTo(map);

        userMarker.bindTooltip('<div class="px-2 py-1 bg-cyan-950 text-cyan-300 text-xs rounded border border-cyan-700 font-medium">📍 আপনি এখানে আছেন</div>', {
            permanent: false,
            direction: 'top',
        });
    }
}

function resetView() {
    if (!map) return;
    if (props.spots.length > 0) {
        const bounds = L.latLngBounds(props.spots.map(s => [s.latitude, s.longitude]));
        map.fitBounds(bounds, { padding: [50, 50], maxZoom: 14 });
    } else {
        map.setView(DHAKA_CENTER, DEFAULT_ZOOM);
    }
}

function panToSelectedSpot() {
    if (!map || !props.selectedSpot) return;
    map.flyTo([props.selectedSpot.latitude, props.selectedSpot.longitude], 15, {
        duration: 0.8,
    });
}

onMounted(() => {
    if (!mapContainer.value) return;

    // Initialize Leaflet Map
    map = L.map(mapContainer.value, {
        center: DHAKA_CENTER,
        zoom: DEFAULT_ZOOM,
        zoomControl: false,
        attributionControl: true,
    });

    // CARTO Dark Matter Tile Layer with API Key
    const cartoKey = import.meta.env.VITE_CARTO_API_KEY || 'cb1_4mbc_1_792ed57cbdb46e8f56c13235';
    const tileUrl = cartoKey
        ? `https://{s}.basemaps.cartocdn.com/rastertiles/dark_all/{z}/{x}/{y}{r}.png?key=${cartoKey}`
        : 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png';

    L.tileLayer(tileUrl, {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors &copy; <a href="https://carto.com/attributions">CARTO</a>',
        subdomains: 'abcd',
        maxZoom: 19,
        className: cartoKey ? '' : 'dark-tiles-layer',
    }).addTo(map);

    // Zoom control at bottom right
    L.control.zoom({ position: 'bottomright' }).addTo(map);

    markersLayer = L.layerGroup().addTo(map);

    // Click handler on map
    map.on('click', (e) => {
        const { lat, lng } = e.latlng;
        emit('map-click', { lat, lng });

        if (props.isAddingSpot) {
            if (tempPinMarker) {
                map.removeLayer(tempPinMarker);
            }
            const pinIcon = L.divIcon({
                className: 'temp-pin-icon',
                html: `
                    <div class="relative flex items-center justify-center animate-bounce">
                        <div class="w-9 h-9 rounded-full bg-emerald-500 text-slate-950 flex items-center justify-center shadow-lg border-2 border-white font-bold text-sm">
                            📍
                        </div>
                    </div>
                `,
                iconSize: [36, 36],
                iconAnchor: [18, 36],
            });
            tempPinMarker = L.marker([lat, lng], { icon: pinIcon }).addTo(map);
        }
    });

    renderMarkers();
    renderUserLocation();

    // Check if initial selected spot exists
    if (props.selectedSpot) {
        panToSelectedSpot();
    }
});

onUnmounted(() => {
    if (map) {
        map.remove();
        map = null;
    }
});

// Watchers
watch(() => props.spots, () => {
    renderMarkers();
}, { deep: true });

watch(() => props.selectedSpot, () => {
    renderMarkers();
    if (props.selectedSpot) {
        panToSelectedSpot();
    }
});

watch(() => props.userLocation, () => {
    renderUserLocation();
    if (props.userLocation && map) {
        map.flyTo([props.userLocation.lat, props.userLocation.lng], 14, { duration: 1.2 });
    }
}, { deep: true });

watch(() => props.isAddingSpot, (newVal) => {
    if (!newVal && tempPinMarker && map) {
        map.removeLayer(tempPinMarker);
        tempPinMarker = null;
    }
});

defineExpose({
    resetView,
    flyTo: (lat, lng, zoom = 15) => {
        if (map) map.flyTo([lat, lng], zoom, { duration: 1 });
    },
});
</script>

<template>
    <div class="relative w-full h-full bg-slate-950 overflow-hidden">
        <!-- Map Container -->
        <div ref="mapContainer" class="w-full h-full z-0"></div>

        <!-- Floating Map Controls (Top Right) -->
        <div class="absolute top-4 right-4 z-[400] flex flex-col gap-2.5">
            <!-- Locate Me Button -->
            <button
                @click="emit('locate-user')"
                type="button"
                title="আমার কাছে খুঁজুন (Near Me)"
                class="group flex items-center gap-2 px-3 py-2 bg-slate-900/90 hover:bg-slate-800 text-slate-200 hover:text-cyan-400 rounded-xl border border-slate-700/80 shadow-xl backdrop-blur-md transition-all active:scale-95 text-xs font-semibold"
            >
                <Crosshair class="w-4 h-4 text-cyan-400 group-hover:rotate-45 transition-transform" />
                <span class="hidden sm:inline">কাছের স্পট (Near Me)</span>
            </button>

            <!-- Reset View Button -->
            <button
                @click="resetView"
                type="button"
                title="পুরো ঢাকা ম্যাপ দেখুন (Reset View)"
                class="flex items-center justify-center p-2.5 bg-slate-900/90 hover:bg-slate-800 text-slate-300 hover:text-white rounded-xl border border-slate-700/80 shadow-xl backdrop-blur-md transition-all active:scale-95"
            >
                <RotateCcw class="w-4 h-4" />
            </button>
        </div>

        <!-- Add Spot Helper Banner when picking coordinates -->
        <div
            v-if="isAddingSpot"
            class="absolute top-4 left-1/2 -translate-x-1/2 z-[400] px-4 py-2.5 bg-emerald-950/90 border border-emerald-500 text-emerald-200 rounded-full shadow-2xl backdrop-blur-md flex items-center gap-2 text-xs sm:text-sm animate-pulse font-medium"
        >
            <MapPin class="w-4 h-4 text-emerald-400 animate-bounce" />
            <span>ম্যাপের যেকোনো জায়গায় ক্লিক করে সঠিক লোকেশন পিন করুন</span>
        </div>

        <!-- Map Legend (Bottom Left) -->
        <div class="absolute bottom-6 left-4 z-[400] hidden sm:flex items-center gap-3 px-3 py-2 bg-slate-900/90 border border-slate-800 rounded-xl shadow-xl backdrop-blur-md text-[11px] text-slate-300">
            <div class="flex items-center gap-1.5">
                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 ring-2 ring-emerald-500/30"></span>
                <span>আসল মজা 🔥 (Verified)</span>
            </div>
            <div class="h-3 w-px bg-slate-800"></div>
            <div class="flex items-center gap-1.5">
                <span class="w-2.5 h-2.5 rounded-full bg-rose-500 ring-2 ring-rose-500/30"></span>
                <span>ব্লগারদের চাপা 🤡 (Overhyped)</span>
            </div>
            <div class="h-3 w-px bg-slate-800"></div>
            <div class="flex items-center gap-1.5">
                <span class="w-2.5 h-2.5 rounded-full bg-amber-400"></span>
                <span>মোটামুটি ⚡</span>
            </div>
        </div>
    </div>
</template>

<style>
/* Leaflet Tooltip Overrides */
.custom-dark-tooltip.leaflet-tooltip {
    background: transparent !important;
    border: none !important;
    box-shadow: none !important;
    padding: 0 !important;
}
.custom-dark-tooltip.leaflet-tooltip::before {
    display: none !important;
}
</style>
