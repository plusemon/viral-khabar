<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreFoodSpotRequest;
use App\Http\Requests\StoreSpotVoteRequest;
use App\Models\FoodSpot;
use App\Models\SpotVote;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class FoodSpotController extends Controller
{
    /**
     * Display a listing of the viral food spots.
     */
    public function index(Request $request): Response|JsonResponse
    {
        $filters = [
            'search' => $request->query('search'),
            'budget_tier' => $request->query('budget_tier', 'all'),
            'category' => $request->query('category', 'all'),
            'vibe' => $request->query('vibe', 'all'),
            'show_scam' => filter_var($request->query('show_scam', false), FILTER_VALIDATE_BOOLEAN),
        ];

        $userLat = $request->filled('lat') ? (float) $request->query('lat') : null;
        $userLng = $request->filled('lng') ? (float) $request->query('lng') : null;

        $spots = FoodSpot::query()
            ->active()
            ->filter($filters)
            ->with(['votes' => function ($q): void {
                $q->latest()->limit(6);
            }])
            ->orderByDesc('trust_score')
            ->orderByDesc('created_at')
            ->get();

        // Calculate distance if coordinates provided
        if ($userLat !== null && $userLng !== null) {
            $spots = $spots->map(function (FoodSpot $spot) use ($userLat, $userLng): FoodSpot {
                $spot->distance = $this->haversineDistance($userLat, $userLng, (float) $spot->latitude, (float) $spot->longitude);

                return $spot;
            })->sortBy('distance')->values();
        }

        $meta = [
            'total_spots' => FoodSpot::active()->count(),
            'verified_hype_count' => FoodSpot::active()->where('trust_score', '>=', 10)->count(),
            'overhyped_scam_count' => FoodSpot::active()->whereColumn('scam_votes', '>', 'hype_votes')->count(),
            'available_categories' => [
                ['id' => 'all', 'label' => 'সব খাবার (All)', 'icon' => 'UtensilsCrossed'],
                ['id' => 'biriyani', 'label' => 'বিরিয়ানি ও তেহারি (Biriyani)', 'icon' => 'Flame'],
                ['id' => 'street_food', 'label' => 'স্ট্রিট ফুড ও ফুচকা (Street Food)', 'icon' => 'Sparkles'],
                ['id' => 'cha_adda', 'label' => 'টংয়ের চা ও আড্ডা (Cha/Tong)', 'icon' => 'Coffee'],
                ['id' => 'kebab_chap', 'label' => 'কাবাব, চাপ ও লুচি (Kebab/Chap)', 'icon' => 'Beef'],
                ['id' => 'dessert', 'label' => 'মিষ্টি ও ফালুদা (Desserts)', 'icon' => 'IceCream'],
            ],
            'budget_tiers' => [
                ['id' => 'all', 'label' => 'সব বাজেট (All)', 'range' => 'যেকোনো দাম'],
                ['id' => 'budget', 'label' => 'ফকিরাপুল ট্রিট (৳০ - ৳১৫০)', 'range' => '< ৳150'],
                ['id' => 'mid', 'label' => 'মিড-রেঞ্জ আড্ডা (৳১৫০ - ৳৪০০)', 'range' => '৳150 - ৳400'],
                ['id' => 'luxury', 'label' => 'বড়লোকি ট্রিট (৳৪০০+)', 'range' => '৳400+'],
            ],
            'vibes' => [
                ['id' => 'all', 'label' => 'সব পরিবেশ'],
                ['id' => 'tong', 'label' => 'টং ও খোলামেলা'],
                ['id' => 'hangout_seating', 'label' => 'বসে আড্ডার পরিবেশ'],
                ['id' => 'late_night', 'label' => 'লেট নাইট ওপেন (রাত ১২টা+)'],
                ['id' => 'takeaway', 'label' => 'পার্সেল / টেকঅ্যাওয়ে'],
            ],
        ];

        if ($request->wantsJson() && ! $request->header('X-Inertia')) {
            return response()->json([
                'spots' => $spots,
                'meta' => $meta,
                'filters' => $filters,
            ]);
        }

        return Inertia::render('Index', [
            'spots' => $spots,
            'filters' => $filters,
            'meta' => $meta,
        ]);
    }

    /**
     * Store a newly created food spot.
     */
    public function store(StoreFoodSpotRequest $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validated();
        $validated['budget_tier'] = FoodSpot::determineBudgetTier((int) $validated['price']);
        $validated['hype_votes'] = 1;
        $validated['meh_votes'] = 0;
        $validated['scam_votes'] = 0;
        $validated['trust_score'] = 2;
        $validated['status'] = 'active';

        $spot = DB::transaction(function () use ($validated, $request): FoodSpot {
            $spot = FoodSpot::create($validated);

            // Record submitter's vote
            $voterHash = $this->generateVoterHash($request);
            SpotVote::create([
                'food_spot_id' => $spot->id,
                'voter_hash' => $voterHash,
                'vote_type' => 'hype',
                'short_review' => 'ম্যাপে নতুন স্পট যোগ করা হয়েছে!',
            ]);

            return $spot;
        });

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'নতুন স্পট সফলভাবে যোগ করা হয়েছে!',
                'spot' => $spot->load('votes'),
            ], 201);
        }

        return redirect()->back()->with('success', 'নতুন স্পট সফলভাবে যোগ করা হয়েছে!');
    }

    /**
     * Cast or update a vote on a food spot.
     */
    public function vote(FoodSpot $foodSpot, StoreSpotVoteRequest $request): RedirectResponse|JsonResponse
    {
        $newVoteType = $request->validated('vote_type');
        $shortReview = $request->validated('short_review');
        $voterHash = $this->generateVoterHash($request);

        DB::transaction(function () use ($foodSpot, $voterHash, $newVoteType, $shortReview): void {
            $existingVote = SpotVote::where('food_spot_id', $foodSpot->id)
                ->where('voter_hash', $voterHash)
                ->first();

            if ($existingVote) {
                if ($existingVote->vote_type !== $newVoteType) {
                    // Decrement old count
                    $oldField = $existingVote->vote_type.'_votes';
                    $foodSpot->{$oldField} = max(0, $foodSpot->{$oldField} - 1);

                    // Increment new count
                    $newField = $newVoteType.'_votes';
                    $foodSpot->{$newField}++;

                    $existingVote->vote_type = $newVoteType;
                }

                if (! empty($shortReview)) {
                    $existingVote->short_review = $shortReview;
                }
                $existingVote->save();
            } else {
                // New vote
                $newField = $newVoteType.'_votes';
                $foodSpot->{$newField}++;

                SpotVote::create([
                    'food_spot_id' => $foodSpot->id,
                    'voter_hash' => $voterHash,
                    'vote_type' => $newVoteType,
                    'short_review' => $shortReview,
                ]);
            }

            $foodSpot->recalculateTrustScore();
            $foodSpot->save();
        });

        $foodSpot->load(['votes' => function ($q): void {
            $q->latest()->limit(6);
        }]);

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'আপনার মতামত সফলভাবে গ্রহণ করা হয়েছে!',
                'spot' => $foodSpot,
            ]);
        }

        return redirect()->back()->with('success', 'আপনার মতামত সফলভাবে গ্রহণ করা হয়েছে!');
    }

    /**
     * Generate unique voter hash without login.
     */
    protected function generateVoterHash(Request $request): string
    {
        $ip = $request->ip() ?? '127.0.0.1';
        $ua = $request->userAgent() ?? 'agent';
        $fingerprint = (string) $request->input('voter_fingerprint', '');

        return hash('sha256', "{$ip}|{$ua}|{$fingerprint}");
    }

    /**
     * Calculate Haversine distance between two coordinates in kilometers.
     */
    protected function haversineDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371; // km
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return round($earthRadius * $c, 2);
    }
}
