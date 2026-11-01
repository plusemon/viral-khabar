<script setup>
import { computed, onMounted, ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import {
    getCategoryEmoji,
    getCategoryLabel,
    getVibeLabel,
    BUDGET_TIERS,
} from '../utils/constants';
import {
    Clock,
    ExternalLink,
    Flame,
    HeartHandshake,
    MapPin,
    MessageSquare,
    Navigation,
    Send,
    Share2,
    ShieldAlert,
    ShieldCheck,
    Sparkles,
    Tag,
    X,
} from 'lucide-vue-next';

const props = defineProps({
    spot: {
        type: Object,
        default: null,
    },
});

const emit = defineEmits(['close', 'demand-treat', 'share-verdict']);

const isSubmittingVote = ref(false);
const shortReview = ref('');
const userVote = ref(null);
const voteSuccessMessage = ref('');

// LocalStorage key for votes
const LOCAL_STORAGE_KEY = 'khabokoi_user_votes_v1';

function loadStoredVote() {
    if (!props.spot) return;
    try {
        const stored = JSON.parse(localStorage.getItem(LOCAL_STORAGE_KEY) || '{}');
        userVote.value = stored[props.spot.id] || null;
    } catch {
        userVote.value = null;
    }
}

function saveStoredVote(spotId, voteType) {
    try {
        const stored = JSON.parse(localStorage.getItem(LOCAL_STORAGE_KEY) || '{}');
        stored[spotId] = voteType;
        localStorage.setItem(LOCAL_STORAGE_KEY, JSON.stringify(stored));
        userVote.value = voteType;
    } catch (e) {
        console.error('Failed to save vote locally', e);
    }
}

async function castVote(voteType) {
    if (!props.spot || isSubmittingVote.value) return;

    isSubmittingVote.value = true;
    voteSuccessMessage.value = '';

    // Generate lightweight client fingerprint
    const fingerprint = `${navigator.userAgent}-${screen.width}x${screen.height}`;

    try {
        router.post(
            `/spots/${props.spot.id}/vote`,
            {
                vote_type: voteType,
                short_review: shortReview.value.trim() || null,
                voter_fingerprint: fingerprint,
            },
            {
                preserveScroll: true,
                preserveState: true,
                onSuccess: () => {
                    saveStoredVote(props.spot.id, voteType);
                    shortReview.value = '';
                    voteSuccessMessage.value = 'আপনার মতামত সফলভাবে গ্রহণ করা হয়েছে! ধন্যবাদ 🎉';
                    setTimeout(() => {
                        voteSuccessMessage.value = '';
                    }, 4000);
                },
                onError: (errors) => {
                    alert(Object.values(errors)[0] || 'ভোট গ্রহণে সমস্যা হয়েছে। আবার চেষ্টা করুন।');
                },
                onFinish: () => {
                    isSubmittingVote.value = false;
                },
            }
        );
    } catch {
        isSubmittingVote.value = false;
    }
}

const googleMapsUrl = computed(() => {
    if (!props.spot) return '#';
    return `https://www.google.com/maps/dir/?api=1&destination=${props.spot.latitude},${props.spot.longitude}`;
});

const budgetTierInfo = computed(() => {
    if (!props.spot) return BUDGET_TIERS.budget;
    return BUDGET_TIERS[props.spot.budget_tier] || BUDGET_TIERS.budget;
});

const isVerifiedHype = computed(() => props.spot && props.spot.trust_score >= 10);
const isOverhypedScam = computed(() => props.spot && props.spot.scam_votes > props.spot.hype_votes);

watch(() => props.spot, () => {
    loadStoredVote();
    voteSuccessMessage.value = '';
    shortReview.value = '';
});

onMounted(() => {
    loadStoredVote();
});
</script>

<template>
    <div
        v-if="spot"
        class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-0 sm:p-4 bg-slate-950/80 backdrop-blur-md transition-opacity duration-300"
        @click.self="emit('close')"
    >
        <!-- Modal Drawer Container -->
        <div
            class="relative w-full sm:max-w-xl max-h-[92vh] sm:max-h-[88vh] bg-slate-900 border border-slate-800 rounded-t-3xl sm:rounded-3xl shadow-2xl flex flex-col overflow-hidden animate-in fade-in slide-in-from-bottom duration-300"
        >
            <!-- Top Drag Handle for mobile -->
            <div class="sm:hidden w-12 h-1.5 bg-slate-700/80 rounded-full mx-auto mt-2.5 mb-1 cursor-pointer" @click="emit('close')"></div>

            <!-- Header Section -->
            <div class="p-5 sm:p-6 pb-4 border-b border-slate-800/80 flex items-start justify-between gap-4">
                <div class="flex-1 min-w-0">
                    <div class="flex flex-wrap items-center gap-2 mb-1.5">
                        <span class="text-2xl">{{ getCategoryEmoji(spot.category) }}</span>
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-800 text-slate-300 border border-slate-700">
                            {{ getCategoryLabel(spot.category) }}
                        </span>
                        <span :class="['px-2.5 py-0.5 rounded-full text-xs font-semibold border', budgetTierInfo.badge]">
                            {{ budgetTierInfo.label }}
                        </span>
                        <span v-if="spot.distance !== undefined" class="px-2 py-0.5 rounded-full text-xs font-bold bg-cyan-950/80 text-cyan-300 border border-cyan-800 flex items-center gap-1">
                            <Navigation class="w-3 h-3 text-cyan-400" />
                            {{ spot.distance }} km দূরে
                        </span>
                    </div>

                    <h2 class="text-xl sm:text-2xl font-black text-white tracking-tight leading-snug">
                        {{ spot.dish_name }}
                    </h2>
                    <p class="text-sm font-semibold text-emerald-400 mt-0.5 flex items-center gap-1">
                        <span>{{ spot.shop_name }}</span>
                    </p>
                    <p class="text-xs text-slate-400 mt-0.5 flex items-center gap-1">
                        <MapPin class="w-3.5 h-3.5 text-slate-500 shrink-0" />
                        <span>{{ spot.area_name }}</span>
                    </p>
                </div>

                <!-- Close Button -->
                <button
                    @click="emit('close')"
                    class="p-2 rounded-xl bg-slate-800/80 hover:bg-slate-700 text-slate-400 hover:text-white transition-colors shrink-0"
                >
                    <X class="w-5 h-5" />
                </button>
            </div>

            <!-- Scrollable Content -->
            <div class="flex-1 overflow-y-auto p-5 sm:p-6 space-y-6">
                <!-- Price & Vibe Quick Stats -->
                <div class="grid grid-cols-3 gap-3">
                    <div class="p-3 rounded-2xl bg-slate-950/60 border border-slate-800 flex flex-col">
                        <span class="text-[11px] text-slate-400 flex items-center gap-1">
                            <Tag class="w-3 h-3 text-slate-400" /> আনুমানিক দাম
                        </span>
                        <span class="text-lg font-black text-emerald-400 mt-1">৳{{ spot.price }}</span>
                    </div>
                    <div class="p-3 rounded-2xl bg-slate-950/60 border border-slate-800 flex flex-col">
                        <span class="text-[11px] text-slate-400 flex items-center gap-1">
                            <Clock class="w-3 h-3 text-slate-400" /> সেরা সময়
                        </span>
                        <span class="text-xs font-semibold text-slate-200 mt-1.5 truncate">
                            {{ spot.best_time || 'যেকোনো সময়' }}
                        </span>
                    </div>
                    <div class="p-3 rounded-2xl bg-slate-950/60 border border-slate-800 flex flex-col">
                        <span class="text-[11px] text-slate-400 flex items-center gap-1">
                            <Sparkles class="w-3 h-3 text-slate-400" /> পরিবেশ
                        </span>
                        <span class="text-xs font-semibold text-slate-200 mt-1.5 truncate">
                            {{ getVibeLabel(spot.vibe) }}
                        </span>
                    </div>
                </div>

                <!-- Community Verdict Badge & Meter -->
                <div class="p-4 rounded-2xl bg-slate-950/80 border border-slate-800/90 space-y-3">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <ShieldCheck v-if="isVerifiedHype" class="w-5 h-5 text-emerald-400 shrink-0" />
                            <ShieldAlert v-else-if="isOverhypedScam" class="w-5 h-5 text-rose-400 shrink-0" />
                            <Sparkles v-else class="w-5 h-5 text-amber-400 shrink-0" />
                            <div>
                                <span class="text-xs font-medium text-slate-400">কমিউনিটি রায় (Community Verdict):</span>
                                <div
                                    :class="[
                                        'text-sm font-black',
                                        isVerifiedHype ? 'text-emerald-400' : (isOverhypedScam ? 'text-rose-400' : 'text-amber-400')
                                    ]"
                                >
                                    {{ spot.verdict_badge.label }}
                                </div>
                            </div>
                        </div>

                        <div class="text-right">
                            <span class="text-[10px] text-slate-400 block uppercase font-mono">Trust Score</span>
                            <span
                                :class="[
                                    'text-lg font-black font-mono',
                                    spot.trust_score >= 10 ? 'text-emerald-400' : (spot.trust_score < 0 ? 'text-rose-400' : 'text-amber-400')
                                ]"
                            >
                                {{ spot.trust_score > 0 ? '+' : '' }}{{ spot.trust_score }}
                            </span>
                        </div>
                    </div>

                    <!-- Multi-segment Progress Bar -->
                    <div class="space-y-1.5">
                        <div class="w-full h-3 bg-slate-800 rounded-full overflow-hidden flex shadow-inner">
                            <div
                                :style="{ width: `${spot.hype_percentage}%` }"
                                class="bg-gradient-to-r from-emerald-500 to-teal-400 h-full transition-all duration-500"
                                :title="`খাঁটি মজা: ${spot.hype_percentage}%`"
                            ></div>
                            <div
                                :style="{ width: `${spot.meh_percentage}%` }"
                                class="bg-gradient-to-r from-amber-500 to-yellow-400 h-full transition-all duration-500"
                                :title="`মোটামুটি: ${spot.meh_percentage}%`"
                            ></div>
                            <div
                                :style="{ width: `${spot.scam_percentage}%` }"
                                class="bg-gradient-to-r from-rose-500 to-red-600 h-full transition-all duration-500"
                                :title="`ব্লগারদের চাপা: ${spot.scam_percentage}%`"
                            ></div>
                        </div>

                        <!-- Vote breakdown numbers -->
                        <div class="flex items-center justify-between text-[11px] font-semibold text-slate-400 pt-0.5">
                            <span class="text-emerald-400 flex items-center gap-1">
                                🔥 আসল মজা: {{ spot.hype_votes }} ({{ spot.hype_percentage }}%)
                            </span>
                            <span class="text-amber-400 flex items-center gap-1">
                                😐 মোটামুটি: {{ spot.meh_votes }} ({{ spot.meh_percentage }}%)
                            </span>
                            <span class="text-rose-400 flex items-center gap-1">
                                🤡 ওভারহাইপড: {{ spot.scam_votes }} ({{ spot.scam_percentage }}%)
                            </span>
                        </div>
                    </div>
                </div>

                <!-- 1-Click Hype vs Reality Voting Box -->
                <div class="p-4 rounded-2xl bg-gradient-to-b from-slate-900 to-slate-950 border border-slate-800 space-y-3">
                    <div class="flex items-center justify-between">
                        <h3 class="text-xs font-bold text-slate-200 uppercase tracking-wider flex items-center gap-1.5">
                            <Flame class="w-4 h-4 text-emerald-400" />
                            আপনি খেয়েছেন? আপনার ভোট দিন (No Login Required)
                        </h3>
                        <span v-if="userVote" class="text-[11px] text-emerald-400 font-semibold bg-emerald-950/80 px-2 py-0.5 rounded-full border border-emerald-800">
                            ভোট দেওয়া হয়েছে
                        </span>
                    </div>

                    <div v-if="voteSuccessMessage" class="p-2.5 rounded-xl bg-emerald-950/90 border border-emerald-600 text-emerald-300 text-xs text-center font-bold animate-pulse">
                        {{ voteSuccessMessage }}
                    </div>

                    <!-- 3 Vote Buttons -->
                    <div class="grid grid-cols-3 gap-2 sm:gap-3">
                        <!-- Hype Vote -->
                        <button
                            @click="castVote('hype')"
                            :disabled="isSubmittingVote"
                            type="button"
                            :class="[
                                'flex flex-col items-center justify-center p-3 rounded-2xl border transition-all active:scale-95 text-center',
                                userVote === 'hype'
                                    ? 'bg-emerald-950/80 border-emerald-500 text-emerald-300 ring-2 ring-emerald-500/50'
                                    : 'bg-slate-900 hover:bg-slate-800 border-slate-700 text-slate-300 hover:border-emerald-500/60'
                            ]"
                        >
                            <span class="text-2xl mb-1">🔥</span>
                            <span class="text-xs font-bold">আসল মজা</span>
                            <span class="text-[10px] text-slate-400">Worth Hype</span>
                        </button>

                        <!-- Meh Vote -->
                        <button
                            @click="castVote('meh')"
                            :disabled="isSubmittingVote"
                            type="button"
                            :class="[
                                'flex flex-col items-center justify-center p-3 rounded-2xl border transition-all active:scale-95 text-center',
                                userVote === 'meh'
                                    ? 'bg-amber-950/80 border-amber-500 text-amber-300 ring-2 ring-amber-500/50'
                                    : 'bg-slate-900 hover:bg-slate-800 border-slate-700 text-slate-300 hover:border-amber-500/60'
                            ]"
                        >
                            <span class="text-2xl mb-1">😐</span>
                            <span class="text-xs font-bold">মোটামুটি</span>
                            <span class="text-[10px] text-slate-400">Average</span>
                        </button>

                        <!-- Scam Vote -->
                        <button
                            @click="castVote('scam')"
                            :disabled="isSubmittingVote"
                            type="button"
                            :class="[
                                'flex flex-col items-center justify-center p-3 rounded-2xl border transition-all active:scale-95 text-center',
                                userVote === 'scam'
                                    ? 'bg-rose-950/80 border-rose-500 text-rose-300 ring-2 ring-rose-500/50'
                                    : 'bg-slate-900 hover:bg-slate-800 border-slate-700 text-slate-300 hover:border-rose-500/60'
                            ]"
                        >
                            <span class="text-2xl mb-1">🤡</span>
                            <span class="text-xs font-bold">ব্লগারদের চাপা</span>
                            <span class="text-[10px] text-slate-400">Overhyped</span>
                        </button>
                    </div>

                    <!-- 1-Line Comment Input -->
                    <div class="relative pt-1">
                        <input
                            v-model="shortReview"
                            type="text"
                            maxlength="140"
                            placeholder="১ লাইনে আপনার আসল অনুভূতি লিখুন (ঐচ্ছিক, সর্বোচ্চ ১৪০ অক্ষর)..."
                            class="w-full px-3.5 py-2.5 text-xs rounded-xl bg-slate-950 border border-slate-800 text-slate-200 placeholder-slate-500 focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 pr-10"
                            @keyup.enter="userVote && castVote(userVote)"
                        />
                        <button
                            v-if="shortReview.trim()"
                            @click="userVote ? castVote(userVote) : castVote('hype')"
                            type="button"
                            title="কমেন্ট সাবমিট করুন"
                            class="absolute right-2 top-3 p-1 text-emerald-400 hover:text-emerald-300"
                        >
                            <Send class="w-4 h-4" />
                        </button>
                    </div>
                </div>

                <!-- Recent Community Reviews -->
                <div v-if="spot.votes && spot.votes.filter(v => v.short_review).length > 0" class="space-y-2.5">
                    <h4 class="text-xs font-bold text-slate-300 flex items-center gap-1.5 uppercase tracking-wider">
                        <MessageSquare class="w-4 h-4 text-cyan-400" />
                        মানুষ কী বলছে (Recent Public Reviews):
                    </h4>
                    <div class="space-y-2">
                        <div
                            v-for="vote in spot.votes.filter(v => v.short_review)"
                            :key="vote.id"
                            class="p-3 rounded-xl bg-slate-950/70 border border-slate-800/80 text-xs text-slate-300"
                        >
                            <div class="flex items-center gap-1.5 mb-1 font-semibold">
                                <span v-if="vote.vote_type === 'hype'" class="text-emerald-400">🔥 আসল মজা:</span>
                                <span v-else-if="vote.vote_type === 'scam'" class="text-rose-400">🤡 ওভারহাইপড:</span>
                                <span v-else class="text-amber-400">😐 মোটামুটি:</span>
                                <span class="text-[10px] text-slate-500 ml-auto">{{ new Date(vote.created_at).toLocaleDateString() }}</span>
                            </div>
                            <p class="text-slate-300 italic">"{{ vote.short_review }}"</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer Action Buttons -->
            <div class="p-4 sm:p-5 bg-slate-950 border-t border-slate-800/80 flex flex-col sm:flex-row gap-2.5">
                <!-- Demand Treat Button (Template 1) -->
                <button
                    @click="emit('demand-treat', spot)"
                    type="button"
                    class="flex-1 flex items-center justify-center gap-2 px-4 py-3 rounded-2xl bg-gradient-to-r from-amber-500 to-orange-600 hover:from-amber-400 hover:to-orange-500 text-slate-950 font-black text-xs sm:text-sm shadow-lg shadow-orange-950/30 active:scale-98 transition-all"
                >
                    <HeartHandshake class="w-4 h-4" />
                    <span>ট্রিট চুক্তিপত্র পাঠাও 📜</span>
                </button>

                <!-- Share Verdict Card (Template 2) -->
                <button
                    @click="emit('share-verdict', spot)"
                    type="button"
                    class="flex-1 flex items-center justify-center gap-2 px-4 py-3 rounded-2xl bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white font-black text-xs sm:text-sm shadow-lg shadow-emerald-950/30 active:scale-98 transition-all"
                >
                    <Share2 class="w-4 h-4" />
                    <span>রিভিউ কার্ড শেয়ার 🎨</span>
                </button>

                <!-- Google Maps Direction Link -->
                <a
                    :href="googleMapsUrl"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="flex items-center justify-center p-3 rounded-2xl bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white transition-all shrink-0"
                    title="Google Maps-এ ডিরেকশন দেখুন"
                >
                    <ExternalLink class="w-4 h-4" />
                </a>
            </div>
        </div>
    </div>
</template>
