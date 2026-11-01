<script setup>
import { computed, reactive, ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import { CATEGORY_MAP, VIBE_MAP, BUDGET_TIERS } from '../utils/constants';
import {
    Clock,
    DollarSign,
    Loader2,
    MapPin,
    MousePointerClick,
    Plus,
    Sparkles,
    Store,
    Utensils,
    X,
} from 'lucide-vue-next';

const props = defineProps({
    initialCoordinates: {
        type: Object,
        default: null,
    },
});

const emit = defineEmits(['close', 'start-pin-mode', 'spot-created']);

const isSubmitting = ref(false);
const errors = ref({});

const form = reactive({
    dish_name: '',
    shop_name: '',
    area_name: '',
    latitude: props.initialCoordinates?.lat || 23.777176,
    longitude: props.initialCoordinates?.lng || 90.399452,
    price: 150,
    category: 'biriyani',
    vibe: 'tong',
    best_time: '',
});

watch(() => props.initialCoordinates, (newCoords) => {
    if (newCoords && newCoords.lat && newCoords.lng) {
        form.latitude = parseFloat(newCoords.lat.toFixed(7));
        form.longitude = parseFloat(newCoords.lng.toFixed(7));
    }
}, { deep: true });

const calculatedBudgetTier = computed(() => {
    const price = parseInt(form.price, 10) || 0;
    if (price < 150) return BUDGET_TIERS.budget;
    if (price <= 400) return BUDGET_TIERS.mid;
    return BUDGET_TIERS.luxury;
});

function submitForm() {
    isSubmitting.value = true;
    errors.value = {};

    router.post('/spots', form, {
        preserveScroll: true,
        onSuccess: (page) => {
            emit('spot-created');
            emit('close');
        },
        onError: (err) => {
            errors.value = err;
        },
        onFinish: () => {
            isSubmitting.value = false;
        },
    });
}
</script>

<template>
    <div
        class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 bg-slate-950/80 backdrop-blur-md overflow-y-auto"
        @click.self="emit('close')"
    >
        <div
            class="relative w-full max-w-lg bg-slate-900 border border-slate-800 rounded-3xl shadow-2xl overflow-hidden my-auto flex flex-col animate-in fade-in zoom-in-95 duration-200"
        >
            <!-- Header -->
            <div class="p-4 sm:p-5 border-b border-slate-800 flex items-center justify-between">
                <div>
                    <h3 class="text-base sm:text-lg font-black text-white flex items-center gap-2">
                        <Plus class="w-5 h-5 text-emerald-400" />
                        নতুন ভাইরাল ফুড স্পট যোগ করুন
                    </h3>
                    <p class="text-xs text-slate-400">আপনার প্রিয় কিংবা সোশ্যাল মিডিয়ায় ভাইরাল স্পটটি ম্যাপে যুক্ত করুন</p>
                </div>

                <button
                    @click="emit('close')"
                    class="p-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-400 hover:text-white transition-colors"
                >
                    <X class="w-5 h-5" />
                </button>
            </div>

            <!-- Form -->
            <form @submit.prevent="submitForm" class="p-4 sm:p-6 space-y-4 overflow-y-auto max-h-[78vh]">
                <!-- Dish Name & Shop Name -->
                <div class="space-y-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-200 mb-1 flex items-center gap-1.5">
                            <Utensils class="w-3.5 h-3.5 text-emerald-400" />
                            খাবারের নাম (Dish Name) *
                        </label>
                        <input
                            v-model="form.dish_name"
                            type="text"
                            required
                            placeholder="যেমন: খাসির গ্লাসি, বিফ ব্রেন চাপ, স্পেশাল মালাই চা"
                            class="w-full px-3.5 py-2.5 text-xs rounded-xl bg-slate-950 border border-slate-700 text-white placeholder-slate-500 focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500"
                        />
                        <span v-if="errors.dish_name" class="text-[11px] text-rose-400 font-semibold mt-1 block">{{ errors.dish_name }}</span>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-200 mb-1 flex items-center gap-1.5">
                            <Store class="w-3.5 h-3.5 text-amber-400" />
                            দোকান বা হোটেলের নাম (Shop Name) *
                        </label>
                        <input
                            v-model="form.shop_name"
                            type="text"
                            required
                            placeholder="যেমন: আল-রাজ্জাক হোটেল, মুস্তাকিম চাপ ঘর, খোকন মামার টং"
                            class="w-full px-3.5 py-2.5 text-xs rounded-xl bg-slate-950 border border-slate-700 text-white placeholder-slate-500 focus:outline-none focus:border-amber-400 focus:ring-1 focus:ring-amber-400"
                        />
                        <span v-if="errors.shop_name" class="text-[11px] text-rose-400 font-semibold mt-1 block">{{ errors.shop_name }}</span>
                    </div>
                </div>

                <!-- Area & Map Coordinates -->
                <div class="space-y-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-200 mb-1 flex items-center gap-1.5">
                            <MapPin class="w-3.5 h-3.5 text-cyan-400" />
                            এলাকার নাম (Area / Location) *
                        </label>
                        <input
                            v-model="form.area_name"
                            type="text"
                            required
                            placeholder="যেমন: পুরান ঢাকা নাজিরা বাজার, মিরপুর ১০, ধানমন্ডি"
                            class="w-full px-3.5 py-2.5 text-xs rounded-xl bg-slate-950 border border-slate-700 text-white placeholder-slate-500 focus:outline-none focus:border-cyan-400 focus:ring-1 focus:ring-cyan-400"
                        />
                        <span v-if="errors.area_name" class="text-[11px] text-rose-400 font-semibold mt-1 block">{{ errors.area_name }}</span>
                    </div>

                    <!-- Coordinates & Map Click Selector -->
                    <div class="p-3 bg-slate-950/70 rounded-2xl border border-slate-800 space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="text-[11px] font-bold text-slate-400">ম্যাপ কোঅর্ডিনেট (GPS Pin):</span>
                            <button
                                @click="emit('start-pin-mode')"
                                type="button"
                                class="text-[11px] font-bold text-emerald-400 hover:text-emerald-300 flex items-center gap-1 bg-emerald-950/70 px-2 py-1 rounded-lg border border-emerald-800 transition-colors"
                            >
                                <MousePointerClick class="w-3 h-3" />
                                <span>ম্যাপে ক্লিক করে পিন করুন</span>
                            </button>
                        </div>

                        <div class="grid grid-cols-2 gap-2 text-xs">
                            <div>
                                <span class="text-[10px] text-slate-500">Latitude:</span>
                                <input
                                    v-model="form.latitude"
                                    type="number"
                                    step="any"
                                    required
                                    class="w-full px-2.5 py-1.5 text-xs rounded-lg bg-slate-900 border border-slate-800 text-slate-300 font-mono"
                                />
                            </div>
                            <div>
                                <span class="text-[10px] text-slate-500">Longitude:</span>
                                <input
                                    v-model="form.longitude"
                                    type="number"
                                    step="any"
                                    required
                                    class="w-full px-2.5 py-1.5 text-xs rounded-lg bg-slate-900 border border-slate-800 text-slate-300 font-mono"
                                />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Price & Auto Tier -->
                <div class="p-3 bg-slate-950/70 rounded-2xl border border-slate-800 space-y-2">
                    <div class="flex items-center justify-between">
                        <label class="text-xs font-bold text-slate-200 flex items-center gap-1.5">
                            <DollarSign class="w-3.5 h-3.5 text-emerald-400" />
                            আনুমানিক দাম (Price in BDT) *
                        </label>
                        <span :class="['text-[10px] font-bold px-2 py-0.5 rounded-full border', calculatedBudgetTier.badge]">
                            {{ calculatedBudgetTier.label }} ({{ calculatedBudgetTier.range }})
                        </span>
                    </div>

                    <div class="relative">
                        <input
                            v-model.number="form.price"
                            type="number"
                            min="0"
                            max="10000"
                            required
                            class="w-full px-3.5 py-2 text-sm rounded-xl bg-slate-900 border border-slate-700 text-white font-mono font-bold focus:outline-none focus:border-emerald-500 pr-10"
                        />
                        <span class="absolute right-3.5 top-2 text-slate-400 font-bold text-xs">৳</span>
                    </div>
                </div>

                <!-- Category & Vibe -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-200 mb-1">ক্যাটাগরি *</label>
                        <select
                            v-model="form.category"
                            class="w-full px-3 py-2 text-xs rounded-xl bg-slate-950 border border-slate-700 text-slate-200 focus:outline-none focus:border-emerald-500"
                        >
                            <option v-for="(cat, key) in CATEGORY_MAP" :key="key" :value="key">
                                {{ cat.emoji }} {{ cat.label }}
                            </option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-200 mb-1">পরিবেশ বা ভাইব *</label>
                        <select
                            v-model="form.vibe"
                            class="w-full px-3 py-2 text-xs rounded-xl bg-slate-950 border border-slate-700 text-slate-200 focus:outline-none focus:border-emerald-500"
                        >
                            <option v-for="(v, key) in VIBE_MAP" :key="key" :value="key">
                                {{ v.icon }} {{ v.label }}
                            </option>
                        </select>
                    </div>
                </div>

                <!-- Best Time -->
                <div>
                    <label class="block text-xs font-bold text-slate-200 mb-1 flex items-center gap-1.5">
                        <Clock class="w-3.5 h-3.5 text-slate-400" />
                        সেরা সময় (ঐচ্ছিক)
                    </label>
                    <input
                        v-model="form.best_time"
                        type="text"
                        placeholder="যেমন: বিকেল ৫টা - রাত ১০টা, দুপুর ১টা - ৪টা"
                        class="w-full px-3.5 py-2 text-xs rounded-xl bg-slate-950 border border-slate-700 text-white placeholder-slate-500 focus:outline-none focus:border-emerald-500"
                    />
                </div>

                <!-- Submit Button -->
                <div class="pt-2">
                    <button
                        type="submit"
                        :disabled="isSubmitting"
                        class="w-full py-3 px-4 rounded-2xl bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-400 hover:to-teal-400 text-slate-950 font-black text-xs sm:text-sm shadow-xl shadow-emerald-950/40 active:scale-98 transition-all flex items-center justify-center gap-2 disabled:opacity-60"
                    >
                        <Loader2 v-if="isSubmitting" class="w-4 h-4 animate-spin" />
                        <Plus v-else class="w-4 h-4" />
                        <span>স্পট যোগ করুন (Add to Radar)</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>
