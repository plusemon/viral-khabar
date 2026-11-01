<script setup>
import { computed, onMounted, ref } from 'vue';
import { getCategoryEmoji } from '../utils/constants';
import {
    Dices,
    Flame,
    HeartHandshake,
    MapPin,
    RotateCw,
    Sparkles,
    Trophy,
    X,
} from 'lucide-vue-next';

const props = defineProps({
    spots: {
        type: Array,
        required: true,
    },
});

const emit = defineEmits(['close', 'select-spot', 'demand-treat']);

const isSpinning = ref(false);
const displayedSpot = ref(null);
const winningSpot = ref(null);

// Only pick spots with decent trust score (> 0) if available
const eligibleSpots = computed(() => {
    const legit = props.spots.filter(s => s.trust_score > 0);
    return legit.length > 0 ? legit : props.spots;
});

function spinRoulette() {
    if (isSpinning.value || eligibleSpots.value.length === 0) return;

    isSpinning.value = true;
    winningSpot.value = null;

    let counter = 0;
    const totalFlips = 28 + Math.floor(Math.random() * 10);
    let speed = 40; // Initial interval ms

    function tick() {
        const randomIndex = Math.floor(Math.random() * eligibleSpots.value.length);
        displayedSpot.value = eligibleSpots.value[randomIndex];
        counter++;

        if (counter < totalFlips) {
            // Decelerate as it approaches the end
            if (counter > totalFlips - 10) {
                speed += 25;
            } else if (counter > totalFlips - 5) {
                speed += 50;
            }
            setTimeout(tick, speed);
        } else {
            // Final winner
            winningSpot.value = displayedSpot.value;
            isSpinning.value = false;
        }
    }

    tick();
}

onMounted(() => {
    spinRoulette();
});
</script>

<template>
    <div
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/85 backdrop-blur-md"
        @click.self="emit('close')"
    >
        <div
            class="relative w-full max-w-md bg-gradient-to-b from-slate-900 to-slate-950 border-2 border-amber-500/50 rounded-3xl shadow-2xl p-6 overflow-hidden flex flex-col items-center text-center animate-in zoom-in-95 duration-200"
        >
            <!-- Header -->
            <div class="w-full flex items-center justify-between pb-3 border-b border-slate-800">
                <div class="flex items-center gap-2">
                    <Dices class="w-5 h-5 text-amber-400 animate-spin" v-if="isSpinning" />
                    <Trophy class="w-5 h-5 text-amber-400" v-else />
                    <span class="text-sm font-black text-white uppercase tracking-wider">
                        আজকে কী খাবো? (Food Roulette)
                    </span>
                </div>
                <button
                    @click="emit('close')"
                    class="p-1.5 rounded-lg bg-slate-800 text-slate-400 hover:text-white"
                >
                    <X class="w-4 h-4" />
                </button>
            </div>

            <!-- Slot Reel Box -->
            <div class="my-6 w-full p-6 rounded-3xl bg-slate-950 border-2 border-dashed border-amber-500/40 relative shadow-inner">
                <!-- Slot Frame Glow -->
                <div
                    :class="[
                        'w-20 h-20 mx-auto rounded-3xl flex items-center justify-center text-4xl mb-4 border-2 shadow-2xl transition-all duration-150',
                        isSpinning
                            ? 'bg-slate-900 border-amber-400/50 scale-110 rotate-6'
                            : 'bg-emerald-950/80 border-emerald-400 glow-emerald scale-100'
                    ]"
                >
                    {{ displayedSpot ? getCategoryEmoji(displayedSpot.category) : '🎰' }}
                </div>

                <div v-if="displayedSpot" class="space-y-1">
                    <h3
                        :class="[
                            'text-xl sm:text-2xl font-black text-white transition-all',
                            isSpinning ? 'blur-[0.5px] opacity-75' : 'text-emerald-400'
                        ]"
                    >
                        {{ displayedSpot.dish_name }}
                    </h3>
                    <p class="text-xs font-bold text-slate-300">
                        {{ displayedSpot.shop_name }}
                    </p>
                    <p class="text-[11px] text-slate-500 flex items-center justify-center gap-1">
                        <MapPin class="w-3 h-3 text-slate-500" />
                        {{ displayedSpot.area_name }} • ৳{{ displayedSpot.price }}
                    </p>
                </div>
                <div v-else class="text-slate-500 text-xs py-4">
                    রুলেট ঘুরছে...
                </div>

                <!-- Winner Tag -->
                <div
                    v-if="winningSpot"
                    class="mt-4 inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-500 text-slate-950 text-xs font-black animate-bounce shadow-lg"
                >
                    <Sparkles class="w-3.5 h-3.5" />
                    <span>আজকের পারফেক্ট ট্রিট পছন্দ!</span>
                </div>
            </div>

            <!-- Actions -->
            <div class="w-full space-y-2.5">
                <button
                    v-if="winningSpot"
                    @click="emit('select-spot', winningSpot); emit('close')"
                    type="button"
                    class="w-full py-3 px-4 rounded-2xl bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-black text-xs sm:text-sm shadow-xl shadow-emerald-950/40 active:scale-98 transition-all flex items-center justify-center gap-2"
                >
                    <Flame class="w-4 h-4" />
                    <span>ম্যাপে এই স্পটটি দেখুন</span>
                </button>

                <div class="flex gap-2 w-full">
                    <button
                        @click="spinRoulette"
                        :disabled="isSpinning"
                        type="button"
                        class="flex-1 py-2.5 px-3 rounded-xl bg-slate-800 hover:bg-slate-700 text-amber-400 font-bold text-xs flex items-center justify-center gap-1.5 transition-all disabled:opacity-50"
                    >
                        <RotateCw :class="['w-3.5 h-3.5', isSpinning && 'animate-spin']" />
                        <span>আবার ঘোরান 🎰</span>
                    </button>

                    <button
                        v-if="winningSpot"
                        @click="emit('demand-treat', winningSpot); emit('close')"
                        type="button"
                        class="flex-1 py-2.5 px-3 rounded-xl bg-amber-500/20 hover:bg-amber-500/30 border border-amber-500/40 text-amber-300 font-bold text-xs flex items-center justify-center gap-1.5 transition-all"
                    >
                        <HeartHandshake class="w-3.5 h-3.5" />
                        <span>ট্রিট চুক্তিপত্র 📜</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
