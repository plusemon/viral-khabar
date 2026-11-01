<script setup>
import { computed, ref } from 'vue';
import * as htmlToImage from 'html-to-image';
import { getCategoryEmoji, getCategoryLabel } from '../utils/constants';
import {
    Check,
    Copy,
    Download,
    FileText,
    Flame,
    HeartHandshake,
    Image as ImageIcon,
    Loader2,
    QrCode,
    Share2,
    ShieldAlert,
    ShieldCheck,
    Sparkles,
    User,
    Users,
    X,
} from 'lucide-vue-next';

const props = defineProps({
    spot: {
        type: Object,
        required: true,
    },
    initialTemplate: {
        type: String,
        default: 'treat', // 'treat' or 'verdict'
    },
});

const emit = defineEmits(['close']);

const currentTemplate = ref(props.initialTemplate);
const friendName = ref('');
const myName = ref('');
const isGenerating = ref(false);
const copySuccess = ref(false);
const cardRef = ref(null);

const displayFriendName = computed(() => friendName.value.trim() || 'প্রিয় বন্ধু / দোস্ত');
const displayMyName = computed(() => myName.value.trim() || 'আমি');

const isVerifiedHype = computed(() => props.spot && props.spot.trust_score >= 10);
const isOverhypedScam = computed(() => props.spot && props.spot.scam_votes > props.spot.hype_votes);

const todayDateFormatted = computed(() => {
    const now = new Date();
    return now.toLocaleDateString('bn-BD', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });
});

async function downloadCard() {
    if (!cardRef.value || isGenerating.value) return;

    isGenerating.value = true;
    try {
        // High resolution 2x scaling
        const dataUrl = await htmlToImage.toPng(cardRef.value, {
            quality: 0.98,
            pixelRatio: 2.5,
            cacheBust: true,
        });

        const link = document.createElement('a');
        const filename = currentTemplate.value === 'treat'
            ? `treat-notice-${props.spot.shop_name.replace(/\s+/g, '-')}.png`
            : `verdict-${props.spot.dish_name.replace(/\s+/g, '-')}.png`;

        link.download = filename;
        link.href = dataUrl;
        link.click();
    } catch (err) {
        console.error('Failed to generate image', err);
        alert('ছবি জেনারেট করতে সমস্যা হয়েছে। আবার চেষ্টা করুন।');
    } finally {
        isGenerating.value = false;
    }
}

async function shareCardNative() {
    if (!cardRef.value || isGenerating.value) return;

    isGenerating.value = true;
    try {
        const dataUrl = await htmlToImage.toPng(cardRef.value, {
            quality: 0.95,
            pixelRatio: 2,
        });

        const blob = await (await fetch(dataUrl)).blob();
        const file = new File([blob], 'khabokoi-treat-card.png', { type: 'image/png' });

        if (navigator.canShare && navigator.canShare({ files: [file] })) {
            await navigator.share({
                title: `${props.spot.dish_name} ট্রিট নোটিশ!`,
                text: `${displayFriendName.value}, ${props.spot.shop_name}-এ ${props.spot.dish_name} খাওয়ানোর চুক্তিপত্র প্রস্তুত!`,
                files: [file],
            });
        } else {
            // Fallback download
            downloadCard();
        }
    } catch (e) {
        console.log('Share dismissed or failed', e);
    } finally {
        isGenerating.value = false;
    }
}

function copyTreatText() {
    const text = `জরুরি ট্রিট নোটিশ: অদ্য শুক্রবার ${displayFriendName.value} আমাকে ${props.spot.shop_name}-এর বিখ্যাত ${props.spot.dish_name} (মূল্য: ৳${props.spot.price}) খাওয়াবে বলে সোশ্যাল মিডিয়ার সামনে অঙ্গীকারবদ্ধ হলো। লোকেশন: ${props.spot.area_name}। এই চুক্তি বাতিলযোগ্য নহে!`;
    navigator.clipboard.writeText(text).then(() => {
        copySuccess.value = true;
        setTimeout(() => {
            copySuccess.value = false;
        }, 3000);
    });
}
</script>

<template>
    <div
        class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 bg-slate-950/85 backdrop-blur-md overflow-y-auto"
        @click.self="emit('close')"
    >
        <div
            class="relative w-full max-w-2xl bg-slate-900 border border-slate-800 rounded-3xl shadow-2xl overflow-hidden my-auto flex flex-col animate-in fade-in zoom-in-95 duration-200"
        >
            <!-- Header -->
            <div class="p-4 sm:p-5 border-b border-slate-800 flex items-center justify-between">
                <div>
                    <h3 class="text-base sm:text-lg font-black text-white flex items-center gap-2">
                        <Sparkles class="w-5 h-5 text-amber-400" />
                        সোশ্যাল মিডিয়া শেয়ার কার্ড জেনারেটর
                    </h3>
                    <p class="text-xs text-slate-400">বন্ধুদের মেনশন করতে বা স্টোরিতে শেয়ার করতে হাই-রেজ কার্ড ডাউনলোড করুন</p>
                </div>

                <button
                    @click="emit('close')"
                    class="p-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-400 hover:text-white transition-colors"
                >
                    <X class="w-5 h-5" />
                </button>
            </div>

            <!-- Template Switcher Tabs -->
            <div class="px-5 pt-4 pb-1 flex items-center gap-2 border-b border-slate-800/60 bg-slate-950/40">
                <button
                    @click="currentTemplate = 'treat'"
                    type="button"
                    :class="[
                        'flex items-center gap-2 px-4 py-2.5 rounded-xl font-bold text-xs transition-all',
                        currentTemplate === 'treat'
                            ? 'bg-amber-500/20 text-amber-400 border border-amber-500/40'
                            : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/50'
                    ]"
                >
                    <HeartHandshake class="w-4 h-4" />
                    <span>১. ট্রিট চুক্তিপত্র (Receipt)</span>
                </button>

                <button
                    @click="currentTemplate = 'verdict'"
                    type="button"
                    :class="[
                        'flex items-center gap-2 px-4 py-2.5 rounded-xl font-bold text-xs transition-all',
                        currentTemplate === 'verdict'
                            ? 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/40'
                            : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/50'
                    ]"
                >
                    <Flame class="w-4 h-4" />
                    <span>২. ফুড রিভিউ কার্ড (Verdict)</span>
                </button>
            </div>

            <div class="p-4 sm:p-6 space-y-5 overflow-y-auto max-h-[75vh]">
                <!-- Customization Inputs for Template 1 -->
                <div v-if="currentTemplate === 'treat'" class="grid grid-cols-1 sm:grid-cols-2 gap-3 p-3.5 bg-slate-950/70 border border-slate-800/80 rounded-2xl">
                    <div>
                        <label class="block text-[11px] font-bold text-slate-300 mb-1 flex items-center gap-1">
                            <Users class="w-3.5 h-3.5 text-amber-400" />
                            বন্ধুর নাম (Friend's Name):
                        </label>
                        <input
                            v-model="friendName"
                            type="text"
                            placeholder="যেমন: তানভীর / দোস্ত"
                            class="w-full px-3 py-2 text-xs rounded-xl bg-slate-900 border border-slate-700 text-white placeholder-slate-500 focus:outline-none focus:border-amber-400"
                        />
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold text-slate-300 mb-1 flex items-center gap-1">
                            <User class="w-3.5 h-3.5 text-emerald-400" />
                            আপনার নাম (Your Name):
                        </label>
                        <input
                            v-model="myName"
                            type="text"
                            placeholder="যেমন: শান্ত / আমি"
                            class="w-full px-3 py-2 text-xs rounded-xl bg-slate-900 border border-slate-700 text-white placeholder-slate-500 focus:outline-none focus:border-emerald-400"
                        />
                    </div>
                </div>

                <!-- CARD PREVIEW AREA (The element exported by html-to-image) -->
                <div class="flex justify-center p-2 sm:p-4 bg-slate-950/90 rounded-2xl border border-slate-800 overflow-hidden">
                    <!-- TEMPLATE 1: Treat Demanding Digital Receipt (ট্রিট চুক্তিপত্র) -->
                    <div
                        v-if="currentTemplate === 'treat'"
                        ref="cardRef"
                        class="w-full max-w-[420px] bg-gradient-to-b from-slate-900 via-slate-900 to-slate-950 text-slate-100 p-6 rounded-3xl border-2 border-amber-500/50 shadow-2xl relative overflow-hidden font-sans select-text"
                    >
                        <!-- Top Receipt Stamp -->
                        <div class="flex items-center justify-between border-b-2 border-dashed border-slate-700/80 pb-4 mb-4">
                            <div class="flex items-center gap-2">
                                <span class="text-3xl">📜</span>
                                <div>
                                    <div class="text-[11px] font-mono tracking-widest text-amber-400 font-bold uppercase">
                                        OFFICIAL NOTICE
                                    </div>
                                    <div class="text-lg font-black tracking-tight text-white">
                                        জরুরি ট্রিট চুক্তিপত্র
                                    </div>
                                </div>
                            </div>

                            <div class="px-2.5 py-1 rounded-lg border border-amber-500/40 bg-amber-500/10 text-amber-300 text-[10px] font-black font-mono rotate-3 uppercase">
                                নং: TRT-{{ spot.id }}{{ Math.floor(Math.random() * 899 + 100) }}
                            </div>
                        </div>

                        <!-- Formal Funny Legal Bangladesh Text -->
                        <div class="space-y-4 my-2 text-xs leading-relaxed text-slate-200">
                            <div class="p-3 rounded-2xl bg-slate-950/80 border border-slate-800">
                                <div class="text-[10px] text-slate-400 uppercase font-mono">আইনগত চুক্তি পক্ষসমূহ:</div>
                                <div class="mt-1 flex items-center justify-between text-xs font-bold">
                                    <span class="text-amber-400">ট্রিট দাতা: <span class="text-white underline">{{ displayFriendName }}</span></span>
                                    <span class="text-slate-500">🤝</span>
                                    <span class="text-emerald-400">ট্রিট গ্রহীতা: <span class="text-white underline">{{ displayMyName }}</span></span>
                                </div>
                            </div>

                            <p class="text-xs text-slate-300">
                                অদ্য শুক্রবার <strong class="text-amber-300 font-bold">{{ displayFriendName }}</strong> নিজের সুস্থ মস্তিষ্কে সোশ্যাল মিডিয়ার সকল শুভাকাঙ্ক্ষীদের সামনে এই মর্মে অঙ্গীকারবদ্ধ হলো যে, সে নিজ খরচে <strong class="text-white">{{ displayMyName }}</strong>-কে নিম্নোক্ত ভাইরাল খাবার খাওয়াইবে:
                            </p>

                            <!-- Receipt Itemized Table -->
                            <div class="border border-slate-800 rounded-2xl overflow-hidden bg-slate-950">
                                <div class="px-3.5 py-2 bg-slate-800/60 flex justify-between text-[10px] font-mono uppercase text-slate-400 border-b border-slate-800">
                                    <span>খাবারের বিবরণ (Item Details)</span>
                                    <span>মূল্য (BDT)</span>
                                </div>
                                <div class="p-3.5 flex justify-between items-start gap-2">
                                    <div>
                                        <div class="font-bold text-sm text-emerald-400">{{ spot.dish_name }}</div>
                                        <div class="text-[11px] text-slate-300 font-medium">{{ spot.shop_name }}</div>
                                        <div class="text-[10px] text-slate-500 mt-0.5">লোকেশন: {{ spot.area_name }}</div>
                                    </div>
                                    <div class="text-base font-black text-white font-mono shrink-0">
                                        ৳{{ spot.price }}
                                    </div>
                                </div>
                                <div class="px-3.5 py-2 bg-slate-900 border-t border-slate-800/80 flex justify-between items-center text-xs font-bold">
                                    <span class="text-slate-400">মোট ধার্য ট্রিট:</span>
                                    <span class="text-emerald-400 font-mono text-sm">৳{{ spot.price }}/-</span>
                                </div>
                            </div>

                            <!-- Legal Fine Print -->
                            <div class="text-[10px] text-slate-400 italic bg-amber-950/20 p-2.5 rounded-xl border border-amber-900/40">
                                ⚠️ বিশেষ শর্ত: "মানিব্যাগ ভুলে বাসায় ফেলে এসেছি" বা "আজকে পেট খারাপ" ধরনের ওজুহাত এই চুক্তি অনুযায়ী বেআইনি এবং শাস্তিযোগ্য অপরাধ বলে গণ্য হবে।
                            </div>
                        </div>

                        <!-- Bottom Stamps & Barcode -->
                        <div class="mt-5 pt-3 border-t-2 border-dashed border-slate-800 flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <div class="w-12 h-12 rounded-xl bg-slate-950 border border-slate-800 flex items-center justify-center p-1">
                                    <QrCode class="w-10 h-10 text-emerald-400" />
                                </div>
                                <div>
                                    <div class="text-[10px] font-mono text-slate-400">{{ todayDateFormatted }}</div>
                                    <div class="text-[9px] font-mono text-emerald-400 tracking-wider">VERIFIED ON KHABOKOI</div>
                                </div>
                            </div>

                            <!-- Red Stamp -->
                            <div class="px-3 py-1.5 rounded-lg border-2 border-rose-500/80 bg-rose-500/10 text-rose-400 font-black text-xs uppercase tracking-wider rotate-[-6deg] shadow">
                                চুক্তি চূড়ান্ত ✔
                            </div>
                        </div>

                        <!-- Barcode Simulation -->
                        <div class="mt-4 pt-2 text-center opacity-70">
                            <div class="font-mono text-xs tracking-widest text-slate-400">||| | ||||| || |||||| | |||| ||| |||||</div>
                            <div class="text-[9px] font-mono text-slate-500">khabokoi.com • ভাইরাল ফুড ও ট্রিট রাডার</div>
                        </div>
                    </div>

                    <!-- TEMPLATE 2: Hype Verdict Social Card (ফুড রিভিউ কার্ড) -->
                    <div
                        v-else
                        ref="cardRef"
                        class="w-full max-w-[420px] aspect-square bg-gradient-to-br from-slate-900 via-slate-950 to-slate-900 text-white p-6 rounded-3xl border-2 border-emerald-500/40 shadow-2xl relative overflow-hidden flex flex-col justify-between select-text"
                    >
                        <!-- Top Header -->
                        <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                            <div class="flex items-center gap-2">
                                <span class="text-2xl">{{ getCategoryEmoji(spot.category) }}</span>
                                <div>
                                    <span class="text-[10px] font-mono tracking-widest text-emerald-400 font-bold uppercase block">
                                        FOOD RADAR VERDICT
                                    </span>
                                    <span class="text-xs font-semibold text-slate-300">
                                        {{ getCategoryLabel(spot.category) }}
                                    </span>
                                </div>
                            </div>

                            <span class="px-2.5 py-1 rounded-full text-xs font-black font-mono bg-slate-800 text-emerald-400 border border-slate-700">
                                ৳{{ spot.price }}
                            </span>
                        </div>

                        <!-- Center Hero: Dish & Verdict Badge -->
                        <div class="my-auto py-2 text-center space-y-3">
                            <h2 class="text-2xl font-black text-white tracking-tight leading-tight">
                                {{ spot.dish_name }}
                            </h2>
                            <p class="text-sm font-semibold text-emerald-400">
                                {{ spot.shop_name }}
                            </p>
                            <p class="text-xs text-slate-400">
                                📍 {{ spot.area_name }}
                            </p>

                            <!-- Big Community Verdict Stamp -->
                            <div class="pt-2">
                                <div
                                    :class="[
                                        'inline-flex items-center gap-2 px-4 py-2 rounded-2xl text-sm font-black uppercase tracking-wide border-2 shadow-xl',
                                        isVerifiedHype
                                            ? 'bg-emerald-500/20 border-emerald-400 text-emerald-300 glow-emerald'
                                            : (isOverhypedScam ? 'bg-rose-500/20 border-rose-500 text-rose-300 glow-rose' : 'bg-amber-500/20 border-amber-400 text-amber-300')
                                    ]"
                                >
                                    <ShieldCheck v-if="isVerifiedHype" class="w-5 h-5 text-emerald-400" />
                                    <ShieldAlert v-else-if="isOverhypedScam" class="w-5 h-5 text-rose-400" />
                                    <Sparkles v-else class="w-5 h-5 text-amber-400" />
                                    <span>{{ spot.verdict_badge.label }}</span>
                                </div>
                            </div>

                            <!-- Verdict Breakdown Bar -->
                            <div class="w-full max-w-xs mx-auto pt-3 space-y-1.5">
                                <div class="w-full h-3 bg-slate-800 rounded-full overflow-hidden flex shadow-inner">
                                    <div
                                        :style="{ width: `${spot.hype_percentage}%` }"
                                        class="bg-emerald-400 h-full"
                                    ></div>
                                    <div
                                        :style="{ width: `${spot.meh_percentage}%` }"
                                        class="bg-amber-400 h-full"
                                    ></div>
                                    <div
                                        :style="{ width: `${spot.scam_percentage}%` }"
                                        class="bg-rose-500 h-full"
                                    ></div>
                                </div>
                                <div class="flex justify-between text-[10px] font-mono text-slate-400">
                                    <span class="text-emerald-400 font-bold">🔥 {{ spot.hype_percentage }}% আসল</span>
                                    <span class="text-rose-400 font-bold">🤡 {{ spot.scam_percentage }}% চাপা</span>
                                </div>
                            </div>
                        </div>

                        <!-- Footer Watermark -->
                        <div class="pt-3 border-t border-slate-800 flex items-center justify-between text-[10px] text-slate-500">
                            <span class="font-mono text-slate-400">khabokoi.com</span>
                            <span class="text-slate-400 font-medium">বাংলাদেশের ভাইরাল খাবারের আসল ম্যাপ</span>
                        </div>
                    </div>
                </div>

                <!-- Action Download Buttons -->
                <div class="flex flex-col sm:flex-row gap-3 pt-2">
                    <button
                        @click="downloadCard"
                        :disabled="isGenerating"
                        type="button"
                        class="flex-1 flex items-center justify-center gap-2 px-5 py-3 rounded-2xl bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-black text-sm shadow-xl shadow-emerald-950/40 active:scale-95 transition-all disabled:opacity-60"
                    >
                        <Loader2 v-if="isGenerating" class="w-4 h-4 animate-spin" />
                        <Download v-else class="w-4 h-4" />
                        <span>PNG কার্ড ডাউনলোড করুন (1-Click)</span>
                    </button>

                    <button
                        @click="shareCardNative"
                        :disabled="isGenerating"
                        type="button"
                        class="flex items-center justify-center gap-2 px-4 py-3 rounded-2xl bg-slate-800 hover:bg-slate-700 text-slate-200 font-bold text-xs sm:text-sm active:scale-95 transition-all"
                        title="শেয়ার করুন"
                    >
                        <Share2 class="w-4 h-4 text-cyan-400" />
                        <span class="hidden sm:inline">সরাসরি শেয়ার</span>
                    </button>

                    <button
                        v-if="currentTemplate === 'treat'"
                        @click="copyTreatText"
                        type="button"
                        class="flex items-center justify-center gap-2 px-4 py-3 rounded-2xl bg-slate-800 hover:bg-slate-700 text-slate-200 font-bold text-xs sm:text-sm active:scale-95 transition-all"
                        title="চুক্তিপত্রের টেক্সট কপি করুন"
                    >
                        <Check v-if="copySuccess" class="w-4 h-4 text-emerald-400" />
                        <Copy v-else class="w-4 h-4 text-amber-400" />
                        <span class="hidden sm:inline">{{ copySuccess ? 'কপি হয়েছে!' : 'মেসেজ কপি' }}</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
