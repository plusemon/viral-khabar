<script setup>
import { computed, ref, watch } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import MapView from '../Components/MapView.vue';
import SpotDetailsModal from '../Components/SpotDetailsModal.vue';
import TreatCardModal from '../Components/TreatCardModal.vue';
import AddSpotModal from '../Components/AddSpotModal.vue';
import RouletteModal from '../Components/RouletteModal.vue';
import {
    CATEGORY_MAP,
    VIBE_MAP,
    BUDGET_TIERS,
    getCategoryEmoji,
} from '../utils/constants';
import {
    AlertTriangle,
    CheckCircle2,
    Crosshair,
    Dices,
    Eye,
    EyeOff,
    Filter,
    Flame,
    HeartHandshake,
    Layers,
    List,
    Map as MapIcon,
    MapPin,
    Navigation,
    Plus,
    Search,
    ShieldAlert,
    ShieldCheck,
    SlidersHorizontal,
    Sparkles,
    Tag,
    X,
} from 'lucide-vue-next';

const props = defineProps({
    spots: {
        type: Array,
        required: true,
    },
    filters: {
        type: Object,
        default: () => ({
            search: '',
            budget_tier: 'all',
            category: 'all',
            vibe: 'all',
            show_scam: false,
        }),
    },
    meta: {
        type: Object,
        default: () => ({}),
    },
});

// State
const searchQuery = ref(props.filters.search || '');
const selectedBudgetTier = ref(props.filters.budget_tier || 'all');
const selectedCategory = ref(props.filters.category || 'all');
const selectedVibe = ref(props.filters.vibe || 'all');
const showScamSpots = ref(props.filters.show_scam || false);

// Active Modals & Selected Spot
const selectedSpot = ref(null);
const showSpotDetails = ref(false);
const showTreatCard = ref(false);
const treatCardTemplate = ref('treat');
const showAddSpot = ref(false);
const showRoulette = ref(false);

// Pin picking mode on map
const isAddingSpotOnMap = ref(false);
const pickedCoordinates = ref(null);

// User Geolocation
const userLocation = ref(null);
const isLocatingUser = ref(false);
const locationErrorMessage = ref('');

// Mobile View Mode ('map' or 'list')
const mobileView = ref('map');

// Toast notifications
const toastMessage = ref('');

function showToast(msg) {
    toastMessage.value = msg;
    setTimeout(() => {
        toastMessage.value = '';
    }, 4000);
}

// Haversine distance calculator in client for real-time reactivity
function calculateDistance(lat1, lon1, lat2, lon2) {
    const R = 6371; // km
    const dLat = (lat2 - lat1) * (Math.PI / 180);
    const dLon = (lon2 - lon1) * (Math.PI / 180);
    const a =
        Math.sin(dLat / 2) * Math.sin(dLat / 2) +
        Math.cos(lat1 * (Math.PI / 180)) * Math.cos(lat2 * (Math.PI / 180)) *
        Math.sin(dLon / 2) * Math.sin(dLon / 2);
    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
    return parseFloat((R * c).toFixed(1));
}

// Reactive filtering on spots
const filteredSpots = computed(() => {
    let result = [...props.spots];

    // Filter by search
    if (searchQuery.value.trim()) {
        const query = searchQuery.value.toLowerCase().trim();
        result = result.filter(spot =>
            spot.dish_name.toLowerCase().includes(query) ||
            spot.shop_name.toLowerCase().includes(query) ||
            spot.area_name.toLowerCase().includes(query)
        );
    }

    // Filter by budget tier
    if (selectedBudgetTier.value !== 'all') {
        result = result.filter(spot => spot.budget_tier === selectedBudgetTier.value);
    }

    // Filter by category
    if (selectedCategory.value !== 'all') {
        result = result.filter(spot => spot.category === selectedCategory.value);
    }

    // Filter by vibe
    if (selectedVibe.value !== 'all') {
        result = result.filter(spot => spot.vibe === selectedVibe.value);
    }

    // Show scam spots toggle (hide trust_score <= -10 by default)
    if (!showScamSpots.value) {
        result = result.filter(spot => spot.trust_score > -10);
    }

    // Calculate distance if user location is available
    if (userLocation.value) {
        result = result.map(spot => ({
            ...spot,
            distance: calculateDistance(
                userLocation.value.lat,
                userLocation.value.lng,
                spot.latitude,
                spot.longitude
            ),
        })).sort((a, b) => (a.distance || 0) - (b.distance || 0));
    }

    return result;
});

// Geolocation trigger
function locateUser() {
    if (!navigator.geolocation) {
        locationErrorMessage.value = 'আপনার ব্রাউজারে লোকেশন সার্ভিস সাপোর্ট করে না।';
        showToast(locationErrorMessage.value);
        return;
    }

    isLocatingUser.value = true;
    locationErrorMessage.value = '';

    navigator.geolocation.getCurrentPosition(
        (position) => {
            userLocation.value = {
                lat: position.coords.latitude,
                lng: position.coords.longitude,
            };
            isLocatingUser.value = false;
            showToast('📍 আপনার লোকেশন সফলভাবে পাওয়া গেছে! কাছের স্পটগুলো সাজানো হলো।');
        },
        (err) => {
            isLocatingUser.value = false;
            locationErrorMessage.value = 'লোকেশন এক্সেস পাওয়া যায়নি। ব্রাউজারের লোকেশন পারমিশন চেক করুন।';
            showToast(locationErrorMessage.value);
        },
        { enableHighAccuracy: true, timeout: 10000, maximumAge: 60000 }
    );
}

// Spot Selection & Modal triggers
function openSpotDetails(spot) {
    selectedSpot.value = spot;
    showSpotDetails.value = true;
}

function handleMapClick(coords) {
    if (isAddingSpotOnMap.value) {
        pickedCoordinates.value = coords;
        isAddingSpotOnMap.value = false;
        showAddSpot.value = true;
        showToast('📍 ম্যাপে লোকেশন পিন করা হয়েছে!');
    }
}

function startMapPinMode() {
    showAddSpot.value = false;
    isAddingSpotOnMap.value = true;
    mobileView.value = 'map';
    showToast('ম্যাপের যেকোনো জায়গায় ক্লিক করে সঠিক পয়েন্টটি সিলেক্ট করুন।');
}

function openTreatCardModal(spot, template = 'treat') {
    selectedSpot.value = spot;
    treatCardTemplate.value = template;
    showSpotDetails.value = false;
    showTreatCard.value = true;
}

function handleSpotCreated() {
    showToast('🎉 নতুন স্পট সফলভাবে যোগ করা হয়েছে!');
    router.reload({ preserveScroll: true });
}

// Reset all filters
function resetFilters() {
    searchQuery.value = '';
    selectedBudgetTier.value = 'all';
    selectedCategory.value = 'all';
    selectedVibe.value = 'all';
    showScamSpots.value = false;
}
</script>

<template>
    <div class="flex flex-col h-screen w-screen bg-slate-950 text-slate-100 font-sans overflow-hidden">
        <Head title="খাবো কই? (Khabo Koi?) - Viral Food & Treat Radar" />

        <!-- Notification Toast -->
        <div
            v-if="toastMessage"
            class="fixed top-4 left-1/2 -translate-x-1/2 z-[9999] px-4 py-2.5 rounded-2xl bg-emerald-950/95 border border-emerald-500 text-emerald-200 text-xs sm:text-sm font-bold shadow-2xl backdrop-blur-md animate-in fade-in slide-in-from-top-4 flex items-center gap-2"
        >
            <CheckCircle2 class="w-4 h-4 text-emerald-400 shrink-0" />
            <span>{{ toastMessage }}</span>
        </div>

        <!-- Main Body: Split Desktop (Sidebar 410px + Map flex-1) / Mobile Tab Switch -->
        <div class="flex flex-1 h-full w-full relative overflow-hidden">

            <!-- ================= LEFT SIDEBAR (Desktop) ================= -->
            <aside
                :class="[
                    'w-full md:w-[410px] lg:w-[440px] h-full flex flex-col bg-slate-950 border-r border-slate-850 z-20 transition-all duration-300 shrink-0 shadow-2xl',
                    mobileView === 'list' ? 'flex' : 'hidden md:flex'
                ]"
            >
                <!-- App Header -->
                <div class="p-4 sm:p-5 border-b border-slate-850/80 bg-slate-950 flex flex-col gap-3">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2.5">
                            <div class="w-10 h-10 rounded-2xl bg-gradient-to-tr from-emerald-500 via-teal-500 to-amber-400 p-0.5 shadow-lg shadow-emerald-950/50">
                                <div class="w-full h-full bg-slate-950 rounded-[14px] flex items-center justify-center text-xl">
                                    🍛
                                </div>
                            </div>
                            <div>
                                <h1 class="text-xl font-black tracking-tight text-white flex items-center gap-1.5 leading-none">
                                    <span>খাবো কই?</span>
                                    <span class="text-[10px] font-mono px-1.5 py-0.5 rounded-md bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 font-bold uppercase">
                                        RADAR
                                    </span>
                                </h1>
                                <p class="text-[11px] font-semibold text-slate-400 mt-0.5">
                                    বাংলাদেশের ভাইরাল ফুড ও ট্রিট ম্যাপ 🇧🇩
                                </p>
                            </div>
                        </div>

                        <!-- Add Spot Button CTA -->
                        <button
                            @click="showAddSpot = true"
                            type="button"
                            class="flex items-center gap-1 px-3 py-2 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-black text-xs shadow-lg shadow-emerald-950/40 active:scale-95 transition-all"
                        >
                            <Plus class="w-4 h-4" />
                            <span>স্পট যোগ (+)</span>
                        </button>
                    </div>

                    <!-- Meta Badges & Random Roulette Trigger -->
                    <div class="flex items-center gap-2 pt-1">
                        <!-- Random Spot Button (আজকে কী খাবো?) -->
                        <button
                            @click="showRoulette = true"
                            type="button"
                            class="flex-1 flex items-center justify-center gap-1.5 py-2 px-3 rounded-xl bg-gradient-to-r from-amber-500/20 via-orange-500/20 to-red-500/20 hover:from-amber-500/30 hover:to-orange-500/30 border border-amber-500/40 text-amber-300 font-bold text-xs shadow-md active:scale-98 transition-all"
                        >
                            <Dices class="w-4 h-4 text-amber-400 animate-spin-slow" />
                            <span>আজকে কী খাবো? 🎰</span>
                        </button>

                        <!-- Near Me Locate Button -->
                        <button
                            @click="locateUser"
                            type="button"
                            title="কাছের স্পট দেখুন"
                            :class="[
                                'py-2 px-3 rounded-xl border text-xs font-bold flex items-center gap-1.5 transition-all active:scale-95',
                                userLocation
                                    ? 'bg-cyan-950/80 border-cyan-500 text-cyan-300'
                                    : 'bg-slate-900 border-slate-700 hover:border-cyan-500/60 text-slate-300 hover:text-cyan-400'
                            ]"
                        >
                            <Crosshair :class="['w-3.5 h-3.5 text-cyan-400', isLocatingUser && 'animate-spin']" />
                            <span class="hidden sm:inline">কাছে</span>
                        </button>
                    </div>

                    <!-- Search Input -->
                    <div class="relative mt-0.5">
                        <Search class="w-4 h-4 text-slate-500 absolute left-3 top-3" />
                        <input
                            v-model="searchQuery"
                            type="text"
                            placeholder="খাবার, দোকান বা এলাকা খুঁজুন (যেমন: কাচ্চি, পুরান ঢাকা)..."
                            class="w-full pl-9 pr-8 py-2.5 text-xs rounded-xl bg-slate-900 border border-slate-800 text-slate-100 placeholder-slate-500 focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 transition-all"
                        />
                        <button
                            v-if="searchQuery"
                            @click="searchQuery = ''"
                            class="absolute right-2.5 top-2.5 text-slate-500 hover:text-white"
                        >
                            <X class="w-4 h-4" />
                        </button>
                    </div>
                </div>

                <!-- "Friday Treat" Smart Filters Section -->
                <div class="p-3 sm:px-4 sm:py-3 border-b border-slate-850/60 bg-slate-950/90 space-y-2.5">
                    <!-- Budget Tier Pills (ট্রিট লেভেল) -->
                    <div>
                        <div class="flex items-center justify-between text-[11px] font-bold text-slate-400 mb-1.5">
                            <span class="flex items-center gap-1">
                                <Tag class="w-3 h-3 text-emerald-400" /> ট্রিট বাজেট লেভেল:
                            </span>
                            <button
                                v-if="selectedBudgetTier !== 'all' || selectedCategory !== 'all' || selectedVibe !== 'all' || searchQuery"
                                @click="resetFilters"
                                class="text-[10px] text-amber-400 hover:underline"
                            >
                                ফিল্টার রিসেট
                            </button>
                        </div>

                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-1.5">
                            <button
                                @click="selectedBudgetTier = 'all'"
                                type="button"
                                :class="[
                                    'py-1.5 px-2 rounded-lg text-[11px] font-bold text-center border transition-all truncate',
                                    selectedBudgetTier === 'all'
                                        ? 'bg-slate-800 border-slate-600 text-white shadow'
                                        : 'bg-slate-900/60 border-slate-800 text-slate-400 hover:text-slate-200'
                                ]"
                            >
                                সব বাজেট
                            </button>

                            <button
                                v-for="(tier, key) in BUDGET_TIERS"
                                :key="key"
                                @click="selectedBudgetTier = key"
                                type="button"
                                :class="[
                                    'py-1.5 px-1.5 rounded-lg text-[10px] font-bold text-center border transition-all truncate',
                                    selectedBudgetTier === key
                                        ? `${tier.badge} shadow`
                                        : 'bg-slate-900/60 border-slate-800 text-slate-400 hover:text-slate-200'
                                ]"
                                :title="`${tier.label} (${tier.range})`"
                            >
                                {{ tier.label.split(' ')[0] }}
                            </button>
                        </div>
                    </div>

                    <!-- Category Pills (Horizontal Scrollable) -->
                    <div class="overflow-x-auto no-scrollbar flex items-center gap-1.5 pt-1 pb-0.5">
                        <button
                            @click="selectedCategory = 'all'"
                            type="button"
                            :class="[
                                'px-2.5 py-1 rounded-full text-[11px] font-bold shrink-0 border transition-all flex items-center gap-1',
                                selectedCategory === 'all'
                                    ? 'bg-emerald-500/20 border-emerald-500/40 text-emerald-300'
                                    : 'bg-slate-900 border-slate-800 text-slate-400 hover:text-slate-200'
                            ]"
                        >
                            <span>🍽️ সব খাবার</span>
                        </button>

                        <button
                            v-for="(cat, key) in CATEGORY_MAP"
                            :key="key"
                            @click="selectedCategory = key"
                            type="button"
                            :class="[
                                'px-2.5 py-1 rounded-full text-[11px] font-bold shrink-0 border transition-all flex items-center gap-1',
                                selectedCategory === key
                                    ? 'bg-emerald-500/20 border-emerald-500/40 text-emerald-300'
                                    : 'bg-slate-900 border-slate-800 text-slate-400 hover:text-slate-200'
                            ]"
                        >
                            <span>{{ cat.emoji }}</span>
                            <span>{{ cat.label.split(' ')[0] }}</span>
                        </button>
                    </div>

                    <!-- Vibe Pills & Overhyped Scam Toggle -->
                    <div class="flex items-center justify-between pt-1 text-[11px]">
                        <!-- Vibe selector -->
                        <div class="flex items-center gap-1">
                            <span class="text-slate-500 text-[10px]">পরিবেশ:</span>
                            <select
                                v-model="selectedVibe"
                                class="bg-slate-900 border border-slate-800 rounded-lg text-[11px] text-slate-300 py-1 px-2 focus:outline-none focus:border-emerald-500"
                            >
                                <option value="all">সব পরিবেশ</option>
                                <option v-for="(v, key) in VIBE_MAP" :key="key" :value="key">
                                    {{ v.icon }} {{ v.label }}
                                </option>
                            </select>
                        </div>

                        <!-- Show Scam/Overhyped Spots Toggle -->
                        <label class="flex items-center gap-1.5 cursor-pointer text-[11px] text-slate-400 hover:text-slate-200 select-none">
                            <input
                                v-model="showScamSpots"
                                type="checkbox"
                                class="rounded bg-slate-900 border-slate-700 text-rose-500 focus:ring-0"
                            />
                            <span :class="showScamSpots ? 'text-rose-400 font-bold' : ''">🤡 চাপা স্পট দেখুন</span>
                        </label>
                    </div>
                </div>

                <!-- Scrollable Spot Cards List -->
                <div class="flex-1 overflow-y-auto p-3 sm:p-4 space-y-2.5">
                    <div class="flex items-center justify-between text-xs text-slate-400 px-1 mb-1 font-semibold">
                        <span>পাওয়া গেছে: <strong class="text-white">{{ filteredSpots.length }}</strong> টি ভাইরাল স্পট</span>
                        <span v-if="userLocation" class="text-cyan-400 text-[10px]">📍 দূরত্ব অনুসারে সাজানো</span>
                    </div>

                    <!-- Empty state -->
                    <div
                        v-if="filteredSpots.length === 0"
                        class="p-8 text-center bg-slate-900/50 rounded-2xl border border-dashed border-slate-800 space-y-2"
                    >
                        <div class="text-3xl">🔍</div>
                        <h4 class="text-sm font-bold text-slate-300">কোনো খাবার স্পট পাওয়া যায়নি</h4>
                        <p class="text-xs text-slate-500">অন্য ফিল্টার দিয়ে চেষ্টা করুন অথবা নতুন স্পট যোগ করুন!</p>
                        <button
                            @click="resetFilters"
                            type="button"
                            class="mt-2 px-3 py-1.5 bg-slate-800 hover:bg-slate-700 text-xs text-emerald-400 rounded-xl font-bold"
                        >
                            সব স্পট দেখুন
                        </button>
                    </div>

                    <!-- Spot Card Items -->
                    <div
                        v-for="spot in filteredSpots"
                        :key="spot.id"
                        @click="openSpotDetails(spot)"
                        :class="[
                            'group p-3.5 rounded-2xl border transition-all cursor-pointer relative overflow-hidden bg-slate-900/80 hover:bg-slate-850 backdrop-blur-md shadow-md active:scale-[0.99]',
                            selectedSpot && selectedSpot.id === spot.id
                                ? 'border-amber-400/80 ring-1 ring-amber-400/50 bg-slate-850'
                                : 'border-slate-800/80 hover:border-slate-700'
                        ]"
                    >
                        <div class="flex items-start justify-between gap-2">
                            <div class="flex items-start gap-2.5 flex-1 min-w-0">
                                <!-- Category Emoji Avatar -->
                                <div
                                    :class="[
                                        'w-11 h-11 rounded-2xl flex items-center justify-center text-2xl border shrink-0 shadow',
                                        spot.trust_score >= 10
                                            ? 'bg-emerald-950/60 border-emerald-500/50 text-white'
                                            : (spot.scam_votes > spot.hype_votes ? 'bg-rose-950/60 border-rose-500/50 text-white' : 'bg-slate-800 border-slate-700 text-white')
                                    ]"
                                >
                                    {{ getCategoryEmoji(spot.category) }}
                                </div>

                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-1.5 flex-wrap">
                                        <h3 class="text-sm font-black text-white group-hover:text-emerald-300 transition-colors truncate">
                                            {{ spot.dish_name }}
                                        </h3>
                                        <span v-if="spot.trust_score >= 10" class="text-[10px] text-emerald-400 font-bold">🔥</span>
                                        <span v-else-if="spot.scam_votes > spot.hype_votes" class="text-[10px] text-rose-400 font-bold">🤡</span>
                                    </div>

                                    <p class="text-xs font-semibold text-emerald-400/90 truncate mt-0.5">
                                        {{ spot.shop_name }}
                                    </p>

                                    <div class="flex items-center gap-1.5 text-[11px] text-slate-400 mt-1">
                                        <MapPin class="w-3 h-3 text-slate-500 shrink-0" />
                                        <span class="truncate">{{ spot.area_name }}</span>
                                        <span v-if="spot.distance !== undefined" class="text-cyan-400 font-bold shrink-0">
                                            • {{ spot.distance }} km
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- Right Side: Price & Trust Badge -->
                            <div class="text-right shrink-0 flex flex-col items-end">
                                <span class="text-sm font-black text-white font-mono">
                                    ৳{{ spot.price }}
                                </span>

                                <span
                                    :class="[
                                        'mt-1 px-2 py-0.5 rounded-full text-[10px] font-bold font-mono border',
                                        spot.trust_score >= 10
                                            ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/30'
                                            : (spot.trust_score < 0 ? 'bg-rose-500/10 text-rose-400 border-rose-500/30' : 'bg-slate-800 text-slate-300 border-slate-700')
                                    ]"
                                >
                                    {{ spot.trust_score > 0 ? '+' : '' }}{{ spot.trust_score }} ট্রাস্ট
                                </span>
                            </div>
                        </div>

                        <!-- Mini Bottom Bar -->
                        <div class="mt-2.5 pt-2 border-t border-slate-800/60 flex items-center justify-between text-[10px] text-slate-400">
                            <div class="flex items-center gap-2">
                                <span class="text-emerald-400 font-medium">🔥 {{ spot.hype_percentage }}% আসল</span>
                                <span v-if="spot.scam_percentage > 0" class="text-rose-400 font-medium">🤡 {{ spot.scam_percentage }}% চাপা</span>
                            </div>

                            <div class="flex items-center gap-1 text-slate-400 group-hover:text-amber-400 font-semibold transition-colors">
                                <HeartHandshake class="w-3 h-3" />
                                <span>ট্রিট চুক্তিপত্র ➜</span>
                            </div>
                        </div>
                    </div>
                </div>
            </aside>

            <!-- ================= RIGHT AREA: INTERACTIVE DARK MAP ================= -->
            <main
                :class="[
                    'flex-1 h-full w-full relative z-10',
                    mobileView === 'list' ? 'hidden md:block' : 'block'
                ]"
            >
                <MapView
                    :spots="filteredSpots"
                    :selected-spot="selectedSpot"
                    :user-location="userLocation"
                    :is-adding-spot="isAddingSpotOnMap"
                    @select-spot="openSpotDetails"
                    @map-click="handleMapClick"
                    @locate-user="locateUser"
                />

                <!-- Mobile Floating Top Pill when in map mode -->
                <div class="md:hidden absolute top-3 left-3 right-3 z-[400] flex items-center justify-between gap-2 p-2 rounded-2xl bg-slate-950/90 border border-slate-800 backdrop-blur-md shadow-2xl">
                    <div class="flex items-center gap-2 flex-1 min-w-0" @click="mobileView = 'list'">
                        <span class="text-xl">🍛</span>
                        <div class="truncate">
                            <h2 class="text-xs font-black text-white truncate">খাবো কই? (Radar)</h2>
                            <p class="text-[10px] text-emerald-400 font-semibold truncate">{{ filteredSpots.length }} টি স্পট একটিভ</p>
                        </div>
                    </div>

                    <button
                        @click="showRoulette = true"
                        class="p-2 rounded-xl bg-amber-500/20 text-amber-300 border border-amber-500/40 text-xs font-bold shrink-0 flex items-center gap-1"
                    >
                        <Dices class="w-3.5 h-3.5" />
                        <span>রুলেট</span>
                    </button>

                    <button
                        @click="showAddSpot = true"
                        class="p-2 rounded-xl bg-emerald-500 text-slate-950 font-black text-xs shrink-0 flex items-center gap-1"
                    >
                        <Plus class="w-3.5 h-3.5" />
                        <span>স্পট</span>
                    </button>
                </div>
            </main>

            <!-- ================= MOBILE VIEW TOGGLER (Floating Bottom Pill) ================= -->
            <div class="md:hidden fixed bottom-5 left-1/2 -translate-x-1/2 z-[450] flex items-center p-1 bg-slate-900/95 border border-slate-700/80 rounded-full shadow-2xl backdrop-blur-md">
                <button
                    @click="mobileView = 'map'"
                    type="button"
                    :class="[
                        'flex items-center gap-1.5 px-4 py-2 rounded-full text-xs font-black transition-all',
                        mobileView === 'map'
                            ? 'bg-emerald-500 text-slate-950 shadow-lg'
                            : 'text-slate-400 hover:text-white'
                    ]"
                >
                    <MapIcon class="w-3.5 h-3.5" />
                    <span>ম্যাপ ভিউ</span>
                </button>

                <button
                    @click="mobileView = 'list'"
                    type="button"
                    :class="[
                        'flex items-center gap-1.5 px-4 py-2 rounded-full text-xs font-black transition-all',
                        mobileView === 'list'
                            ? 'bg-emerald-500 text-slate-950 shadow-lg'
                            : 'text-slate-400 hover:text-white'
                    ]"
                >
                    <List class="w-3.5 h-3.5" />
                    <span>লিস্ট ({{ filteredSpots.length }})</span>
                </button>
            </div>
        </div>

        <!-- ================= MODALS & DRAWERS ================= -->

        <!-- 1. Spot Details Drawer / Modal -->
        <SpotDetailsModal
            v-if="showSpotDetails && selectedSpot"
            :spot="selectedSpot"
            @close="showSpotDetails = false"
            @demand-treat="(spot) => openTreatCardModal(spot, 'treat')"
            @share-verdict="(spot) => openTreatCardModal(spot, 'verdict')"
        />

        <!-- 2. Viral Social Card Generator Modal -->
        <TreatCardModal
            v-if="showTreatCard && selectedSpot"
            :spot="selectedSpot"
            :initial-template="treatCardTemplate"
            @close="showTreatCard = false"
        />

        <!-- 3. Add Spot Modal -->
        <AddSpotModal
            v-if="showAddSpot"
            :initial-coordinates="pickedCoordinates"
            @close="showAddSpot = false"
            @start-pin-mode="startMapPinMode"
            @spot-created="handleSpotCreated"
        />

        <!-- 4. Food Roulette Modal ("আজকে কী খাবো?") -->
        <RouletteModal
            v-if="showRoulette"
            :spots="filteredSpots"
            @close="showRoulette = false"
            @select-spot="openSpotDetails"
            @demand-treat="(spot) => openTreatCardModal(spot, 'treat')"
        />
    </div>
</template>

<style>
/* Hide horizontal scrollbar */
.no-scrollbar::-webkit-scrollbar {
    display: none;
}
.no-scrollbar {
    -ms-overflow-style: none;
    scrollbar-width: none;
}

@keyframes spinSlow {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}
.animate-spin-slow {
    animation: spinSlow 6s linear infinite;
}
</style>
