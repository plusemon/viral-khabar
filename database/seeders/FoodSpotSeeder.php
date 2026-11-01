<?php

namespace Database\Seeders;

use App\Models\FoodSpot;
use App\Models\SpotVote;
use Illuminate\Database\Seeder;

class FoodSpotSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $spots = [
            [
                'dish_name' => 'খাসির গ্লাসি ও স্পেশাল নান',
                'shop_name' => 'আল-রাজ্জাক হোটেল অ্যান্ড রেস্টুরেন্ট',
                'area_name' => 'পুরান ঢাকা (নাজিরা বাজার)',
                'latitude' => 23.7176100,
                'longitude' => 90.4042200,
                'price' => 380,
                'category' => 'biriyani',
                'vibe' => 'late_night',
                'best_time' => 'রাত ৯টা - রাত ১টা',
                'hype_votes' => 45,
                'meh_votes' => 5,
                'scam_votes' => 3,
                'reviews' => [
                    ['vote_type' => 'hype', 'short_review' => 'খাসির মাংস তুলার মতো নরম, নানের সাথে ঝোলের কম্বিনেশন অমৃত!'],
                    ['vote_type' => 'hype', 'short_review' => 'লেট নাইট পুরান ঢাকা আসলে আল-রাজ্জাক মাস্ট স্টপ।'],
                    ['vote_type' => 'meh', 'short_review' => 'তেল একটু বেশি মনে হলো, তবে স্বাদ ভালো।'],
                ],
            ],
            [
                'dish_name' => 'বিফ ব্রেন চাপ ও স্পেশাল গরম লুচি',
                'shop_name' => 'মুস্তাকিম ভ্যারাইটিজ অ্যান্ড চাপ ঘর',
                'area_name' => 'মোহাম্মদপুর টাউন হল (বিহারী ক্যাম্প)',
                'latitude' => 23.7588200,
                'longitude' => 90.3644100,
                'price' => 180,
                'category' => 'kebab_chap',
                'vibe' => 'street_food',
                'best_time' => 'সন্ধ্যা ৬টা - রাত ১১টা',
                'hype_votes' => 52,
                'meh_votes' => 8,
                'scam_votes' => 4,
                'reviews' => [
                    ['vote_type' => 'hype', 'short_review' => 'মুস্তাকিমের মগজ চাপ আর গরম ফুলকো লুচির কোনো তুলনা নাই।'],
                    ['vote_type' => 'hype', 'short_review' => 'ভিড় বেশি থাকে কিন্তু টেস্ট খাঁটি পুরান আমলের বিহারী স্টাইল।'],
                ],
            ],
            [
                'dish_name' => 'স্পেশাল মালাই চা ও বিস্কুট',
                'shop_name' => 'খোকন মামার চায়ের টং',
                'area_name' => 'ঢাকা বিশ্ববিদ্যালয় (টিএসসি)',
                'latitude' => 23.7324300,
                'longitude' => 90.3958100,
                'price' => 30,
                'category' => 'cha_adda',
                'vibe' => 'tong',
                'best_time' => 'বিকেল ৪টা - রাত ১০টা',
                'hype_votes' => 68,
                'meh_votes' => 12,
                'scam_votes' => 2,
                'reviews' => [
                    ['vote_type' => 'hype', 'short_review' => 'টিএসসির আড্ডা মানেই খোকন মামার মালাইয়ের ঘন স্তর দেওয়া স্পেশাল চা!'],
                    ['vote_type' => 'hype', 'short_review' => 'দাম অনুযায়ী টেস্ট ফাটাফাটি। বন্ধুদের নিয়ে বসার আসল জায়গা।'],
                ],
            ],
            [
                'dish_name' => 'স্পেশাল শাহী ফালুদা ও লেবুর শরবত',
                'shop_name' => 'বিউটি লাচ্ছি অ্যান্ড ফালুদা',
                'area_name' => 'নাজিরা বাজার, পুরান ঢাকা',
                'latitude' => 23.7145200,
                'longitude' => 90.4027400,
                'price' => 120,
                'category' => 'dessert',
                'vibe' => 'takeaway',
                'best_time' => 'দুপুর ১২টা - রাত ৯টা',
                'hype_votes' => 58,
                'meh_votes' => 6,
                'scam_votes' => 2,
                'reviews' => [
                    ['vote_type' => 'hype', 'short_review' => 'ঐতিহাসিক স্বাদ, গরমের দিনে বিউটির ফালুদা খেলে মন জুড়িয়ে যায়।'],
                ],
            ],
            [
                'dish_name' => 'বট ও বিফ খিরি কাবাব সাথে বাটার নান',
                'shop_name' => 'শওকত কাবাব ঘর',
                'area_name' => 'মিরপুর ১০ (বেনারসি পল্লী)',
                'latitude' => 23.8073100,
                'longitude' => 90.3687200,
                'price' => 220,
                'category' => 'kebab_chap',
                'vibe' => 'hangout_seating',
                'best_time' => 'সন্ধ্যা ৭টা - রাত ১২টা',
                'hype_votes' => 39,
                'meh_votes' => 7,
                'scam_votes' => 5,
                'reviews' => [
                    ['vote_type' => 'hype', 'short_review' => 'বট ফ্রাইটা অসাধারণ ক্রাঞ্চি আর মশলাদার!'],
                ],
            ],
            [
                'dish_name' => 'কাচ্চি বিরিয়ানি প্ল্যাটার উইথ বোরহানি',
                'shop_name' => "সুলতান'স ডাইন (Sultan's Dine)",
                'area_name' => 'ধানমন্ডি (জিগাতলা মোড়)',
                'latitude' => 23.7371500,
                'longitude' => 90.3725300,
                'price' => 499,
                'category' => 'biriyani',
                'vibe' => 'hangout_seating',
                'best_time' => 'দুপুর ১টা - রাত ১০টা',
                'hype_votes' => 85,
                'meh_votes' => 15,
                'scam_votes' => 9,
                'reviews' => [
                    ['vote_type' => 'hype', 'short_review' => 'অফিস বা ফ্রেন্ডদের কাচ্চি ট্রিটের জন্য সবসময় এক নম্বর চয়েস।'],
                    ['vote_type' => 'meh', 'short_review' => 'আগে আলু সাইজে বড় ছিল, এখন কোয়ান্টিটি একটু কমেছে।'],
                ],
            ],
            [
                'dish_name' => 'চুইঝাল স্পেশাল গরুর মাংস ও গরম ভাত',
                'shop_name' => 'আপেল মাহমুদ চুইঝাল অ্যান্ড ক্যাফে',
                'area_name' => 'খিলগাঁও তালতলা',
                'latitude' => 23.7534400,
                'longitude' => 90.4221100,
                'price' => 320,
                'category' => 'street_food',
                'vibe' => 'hangout_seating',
                'best_time' => 'দুপুর ১২:৩০ - বিকেল ৪টা',
                'hype_votes' => 34,
                'meh_votes' => 9,
                'scam_votes' => 4,
                'reviews' => [
                    ['vote_type' => 'hype', 'short_review' => 'চুইঝালের ঝাঁজ আর সুঘ্রাণ জিভে জল এনে দেয়। ঝালপ্রেমীদের স্বর্গ!'],
                ],
            ],
            [
                'dish_name' => 'স্পেশাল দই ফুচকা ও তেঁতুল চটপটি',
                'shop_name' => 'ক্যাপিটাল সিরাজ চটপটি',
                'area_name' => 'বেইলি রোড (ক্যাপিটাল মার্কেট)',
                'latitude' => 23.7417500,
                'longitude' => 90.4089300,
                'price' => 110,
                'category' => 'street_food',
                'vibe' => 'street_food',
                'best_time' => 'বিকেল ৫টা - রাত ৯:৩০',
                'hype_votes' => 47,
                'meh_votes' => 5,
                'scam_votes' => 2,
                'reviews' => [
                    ['vote_type' => 'hype', 'short_review' => 'বেইলি রোডের দই ফুচকা মানেই সিরাজ ভাই। টক-মিষ্টি ব্যালেন্স সেরা!'],
                ],
            ],
            [
                'dish_name' => 'স্পেশাল খাসির হালিম ও ধনেপাতা সালাদ',
                'shop_name' => 'ঢাকা মামা হালিম',
                'area_name' => 'কলাবাগান (ডলফিন গলি)',
                'latitude' => 23.7483200,
                'longitude' => 90.3812200,
                'price' => 160,
                'category' => 'street_food',
                'vibe' => 'takeaway',
                'best_time' => 'বিকেল ৩টা - রাত ৮টা',
                'hype_votes' => 36,
                'meh_votes' => 8,
                'scam_votes' => 3,
                'reviews' => [
                    ['vote_type' => 'hype', 'short_review' => 'হালিমের ডাল ও মাংসের টেক্সচার একদম পারফেক্ট। খাঁটি বাটার স্মেল।'],
                ],
            ],
            [
                'dish_name' => 'আগুন ফুচকা ও চিজ ব্লাস্ট ফুচকা',
                'shop_name' => 'ফুচকা লাভার্স স্টল',
                'area_name' => 'উত্তরা সেক্টর ১১ (জমজম মোড়)',
                'latitude' => 23.8742100,
                'longitude' => 90.3857300,
                'price' => 180,
                'category' => 'street_food',
                'vibe' => 'street_food',
                'best_time' => 'বিকেল ৫টা - রাত ১০টা',
                'hype_votes' => 6,
                'meh_votes' => 10,
                'scam_votes' => 28,
                'reviews' => [
                    ['vote_type' => 'scam', 'short_review' => 'ফুড ব্লগারদের ভিডিও দেখে এসে চরম হতাশ! আগুন দিয়ে জাস্ট নাটক, টেস্ট জঘন্য!'],
                    ['vote_type' => 'scam', 'short_review' => 'এত বিশ্রী চিজের গন্ধ আর দামও অতিরিক্ত বেশি। পুরাই স্ক্যাম!'],
                ],
            ],
            [
                'dish_name' => 'হাঁসের মাংস ও সরিষার তেল তেহারি',
                'shop_name' => 'লালবাগ কেল্লা ঐতিহ্যবাহী তেহারি',
                'area_name' => 'লালবাগ কেল্লা রোড',
                'latitude' => 23.7197400,
                'longitude' => 90.3881200,
                'price' => 140,
                'category' => 'biriyani',
                'vibe' => 'tong',
                'best_time' => 'দুপুর ১২টা - বিকেল ৩টা',
                'hype_votes' => 42,
                'meh_votes' => 4,
                'scam_votes' => 1,
                'reviews' => [
                    ['vote_type' => 'hype', 'short_review' => 'হাঁসের মাংসের ঝাঁজালো স্বাদ আর ধোঁয়া ওঠা চালের সুবাস!'],
                ],
            ],
            [
                'dish_name' => 'স্পেশাল মাটন কাচ্চি ও খাসির রেজালা',
                'shop_name' => 'স্টার হোটেল অ্যান্ড রেস্টুরেন্ট',
                'area_name' => 'কারওয়ান বাজার',
                'latitude' => 23.7511200,
                'longitude' => 90.3934300,
                'price' => 310,
                'category' => 'biriyani',
                'vibe' => 'hangout_seating',
                'best_time' => 'দুপুর ১টা - রাত ১০টা',
                'hype_votes' => 65,
                'meh_votes' => 11,
                'scam_votes' => 5,
                'reviews' => [
                    ['vote_type' => 'hype', 'short_review' => 'বহু বছরের বিশ্বস্ত স্বাদ। স্টারের রেজালা আর কাচ্চি অলটাইম উইনার।'],
                ],
            ],
            [
                'dish_name' => 'ক্রিস্পি বাবল ওয়াফেল উইথ ন্যুটেলা ও আইসক্রিম',
                'shop_name' => 'ওয়াফেল টাইম ক্যাফে',
                'area_name' => 'বনানী (রোড ১১)',
                'latitude' => 23.7937400,
                'longitude' => 90.4055200,
                'price' => 420,
                'category' => 'dessert',
                'vibe' => 'hangout_seating',
                'best_time' => 'বিকেল ৪টা - রাত ১১টা',
                'hype_votes' => 25,
                'meh_votes' => 8,
                'scam_votes' => 3,
                'reviews' => [
                    ['vote_type' => 'hype', 'short_review' => 'ওয়াফেল একদম ফ্রেশ আর ক্রাঞ্চি ছিল। ডেজার্ট লাভারদের জন্য মাস্ট।'],
                ],
            ],
            [
                'dish_name' => 'মশলাদার ফ্রাইড চিকেন চাপ ও রুমালি রুটি',
                'shop_name' => 'শান্তিনগর সুপার চাপ কর্নার',
                'area_name' => 'শান্তিনগর মোড়',
                'latitude' => 23.7389100,
                'longitude' => 90.4158400,
                'price' => 170,
                'category' => 'kebab_chap',
                'vibe' => 'late_night',
                'best_time' => 'রাত ৮টা - রাত ২টা',
                'hype_votes' => 30,
                'meh_votes' => 6,
                'scam_votes' => 2,
                'reviews' => [
                    ['vote_type' => 'hype', 'short_review' => 'লেট নাইট হাংরি আওয়ার্সে এর চেয়ে ভালো চাপ আর কিছু হতে পারে না।'],
                ],
            ],
            [
                'dish_name' => 'লেকের পাড়ের ধোঁয়া ওঠা রঙ চা ও স্পেশাল চটপটি',
                'shop_name' => 'লেক ভিউ টং আড্ডা',
                'area_name' => 'ধানমন্ডি ৮/এ (রবীন্দ্র সরোবর)',
                'latitude' => 23.7485200,
                'longitude' => 90.3768100,
                'price' => 60,
                'category' => 'cha_adda',
                'vibe' => 'tong',
                'best_time' => 'বিকেল ৪:৩০ - রাত ৯টা',
                'hype_votes' => 50,
                'meh_votes' => 10,
                'scam_votes' => 3,
                'reviews' => [
                    ['vote_type' => 'hype', 'short_review' => 'বিকেলের মুক্ত হাওয়া সাথে টংয়ের গরম চা আর চটপটি—শহুরে শান্তির জায়গা।'],
                ],
            ],
            [
                'dish_name' => 'স্পেশাল ভুনা খিচুড়ি ও গরুর মাংস',
                'shop_name' => 'বশির রেস্তোরাঁ',
                'area_name' => 'পুরানা পল্টন কালভার্ট রোড',
                'latitude' => 23.7311400,
                'longitude' => 90.4132100,
                'price' => 260,
                'category' => 'street_food',
                'vibe' => 'hangout_seating',
                'best_time' => 'সকাল ৭টা - দুপুর ৩টা',
                'hype_votes' => 38,
                'meh_votes' => 5,
                'scam_votes' => 2,
                'reviews' => [
                    ['vote_type' => 'hype', 'short_review' => 'বৃষ্টির দিনে কিংবা সকালে বশিরের গরম খিচুড়ির তুলনা নেই।'],
                ],
            ],
            [
                'dish_name' => 'চকলেট চিজ মোমো ও হট সস',
                'shop_name' => 'ভাইরাল ক্রাঞ্চ ফুড কার্ট',
                'area_name' => 'মিরপুর স্টেডিয়াম ২ নম্বর গেট',
                'latitude' => 23.8055200,
                'longitude' => 90.3621400,
                'price' => 240,
                'category' => 'street_food',
                'vibe' => 'street_food',
                'best_time' => 'বিকেল ৫টা - রাত ১০টা',
                'hype_votes' => 4,
                'meh_votes' => 7,
                'scam_votes' => 19,
                'reviews' => [
                    ['vote_type' => 'scam', 'short_review' => 'চকলেটের সাথে মোমোর কোনো মিলন হতে পারে না! ফালতু এক্সপেরিমেন্ট, টাকা জলে গেল।'],
                    ['vote_type' => 'scam', 'short_review' => 'টিকটকারদের কথা শুনে ট্রাই করেছিলাম, চরম বাজে লেগেছে।'],
                ],
            ],
        ];

        foreach ($spots as $index => $spotData) {
            $reviews = $spotData['reviews'] ?? [];
            unset($spotData['reviews']);

            $spotData['budget_tier'] = FoodSpot::determineBudgetTier((int) $spotData['price']);
            $spotData['trust_score'] = ($spotData['hype_votes'] * 2) + ($spotData['meh_votes'] * 0) - ($spotData['scam_votes'] * 3);
            $spotData['status'] = 'active';

            $spot = FoodSpot::create($spotData);

            foreach ($reviews as $revIndex => $rev) {
                SpotVote::create([
                    'food_spot_id' => $spot->id,
                    'voter_hash' => hash('sha256', "seed_voter_{$index}_{$revIndex}"),
                    'vote_type' => $rev['vote_type'],
                    'short_review' => $rev['short_review'],
                ]);
            }
        }
    }
}
