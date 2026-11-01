export const CATEGORY_MAP = {
    biriyani: { label: 'বিরিয়ানি ও মাংস', emoji: '🍛', color: 'from-amber-500 to-orange-600' },
    street_food: { label: 'স্ট্রিট ফুড ও ফুচকা', emoji: '🌶️', color: 'from-red-500 to-rose-600' },
    cha_adda: { label: 'টংয়ের চা ও আড্ডা', emoji: '☕', color: 'from-emerald-500 to-teal-600' },
    kebab_chap: { label: 'কাবাব, চাপ ও লুচি', emoji: '🍢', color: 'from-orange-500 to-red-600' },
    dessert: { label: 'মিষ্টি ও ফালুদা', emoji: '🍧', color: 'from-pink-500 to-purple-600' },
};

export const VIBE_MAP = {
    tong: { label: 'টং ও খোলামেলা', icon: '🏮' },
    hangout_seating: { label: 'বসে আড্ডার পরিবেশ', icon: '🪑' },
    late_night: { label: 'লেট নাইট ওপেন', icon: '🌙' },
    takeaway: { label: 'পার্সেল / টেকঅ্যাওয়ে', icon: '📦' },
};

export const BUDGET_TIERS = {
    budget: { label: 'ফকিরাপুল ট্রিট', range: '< ৳১৫০', badge: 'bg-emerald-500/20 text-emerald-400 border-emerald-500/30' },
    mid: { label: 'মিড-রেঞ্জ আড্ডা', range: '৳১৫০ - ৳৪০০', badge: 'bg-sky-500/20 text-sky-400 border-sky-500/30' },
    luxury: { label: 'বড়লোকি ট্রিট', range: '৳৪০০+', badge: 'bg-amber-500/20 text-amber-400 border-amber-500/30' },
};

export function getCategoryEmoji(category) {
    return CATEGORY_MAP[category]?.emoji || '🍽️';
}

export function getCategoryLabel(category) {
    return CATEGORY_MAP[category]?.label || 'অন্যান্য';
}

export function getVibeLabel(vibe) {
    return VIBE_MAP[vibe]?.label || vibe;
}
