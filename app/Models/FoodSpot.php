<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FoodSpot extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'dish_name',
        'shop_name',
        'area_name',
        'latitude',
        'longitude',
        'price',
        'budget_tier',
        'category',
        'vibe',
        'best_time',
        'hype_votes',
        'meh_votes',
        'scam_votes',
        'trust_score',
        'status',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var list<string>
     */
    protected $appends = [
        'total_votes',
        'hype_percentage',
        'scam_percentage',
        'meh_percentage',
        'verdict_badge',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'latitude' => 'float',
            'longitude' => 'float',
            'price' => 'integer',
            'hype_votes' => 'integer',
            'meh_votes' => 'integer',
            'scam_votes' => 'integer',
            'trust_score' => 'integer',
        ];
    }

    /**
     * Get votes relationship.
     */
    public function votes(): HasMany
    {
        return $this->hasMany(SpotVote::class);
    }

    /**
     * Scope a query to only include active spots.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope a query based on request filters.
     *
     * @param  array<string, mixed>  $filters
     */
    public function scopeFilter(Builder $query, array $filters = []): Builder
    {
        if (! empty($filters['search'])) {
            $search = trim((string) $filters['search']);
            $query->where(function (Builder $q) use ($search): void {
                $q->where('dish_name', 'like', "%{$search}%")
                    ->orWhere('shop_name', 'like', "%{$search}%")
                    ->orWhere('area_name', 'like', "%{$search}%");
            });
        }

        if (! empty($filters['budget_tier']) && $filters['budget_tier'] !== 'all') {
            $query->where('budget_tier', $filters['budget_tier']);
        }

        if (! empty($filters['category']) && $filters['category'] !== 'all') {
            $query->where('category', $filters['category']);
        }

        if (! empty($filters['vibe']) && $filters['vibe'] !== 'all') {
            $query->where('vibe', $filters['vibe']);
        }

        // Unless specifically requested to show severely scam/overhyped spots,
        // hide spots with trust_score <= -10 by default
        $showScam = filter_var($filters['show_scam'] ?? false, FILTER_VALIDATE_BOOLEAN);
        if (! $showScam) {
            $query->where('trust_score', '>', -10);
        }

        return $query;
    }

    /**
     * Total votes count.
     */
    public function getTotalVotesAttribute(): int
    {
        return (int) ($this->hype_votes + $this->meh_votes + $this->scam_votes);
    }

    /**
     * Hype percentage.
     */
    public function getHypePercentageAttribute(): int
    {
        $total = $this->total_votes;
        if ($total === 0) {
            return 0;
        }

        return (int) round(($this->hype_votes / $total) * 100);
    }

    /**
     * Scam/Overhyped percentage.
     */
    public function getScamPercentageAttribute(): int
    {
        $total = $this->total_votes;
        if ($total === 0) {
            return 0;
        }

        return (int) round(($this->scam_votes / $total) * 100);
    }

    /**
     * Meh percentage.
     */
    public function getMehPercentageAttribute(): int
    {
        $total = $this->total_votes;
        if ($total === 0) {
            return 0;
        }

        return (int) round(($this->meh_votes / $total) * 100);
    }

    /**
     * Dynamic verdict badge info.
     *
     * @return array<string, string>
     */
    public function getVerdictBadgeAttribute(): array
    {
        if ($this->trust_score >= 10) {
            return [
                'type' => 'verified',
                'label' => '১০০% খাঁটি মজা 🔥',
                'description' => 'Verified Hype - জনগণ সার্টিফাইড!',
                'color' => 'emerald',
            ];
        }

        if ($this->scam_votes > $this->hype_votes) {
            return [
                'type' => 'scam',
                'label' => 'সাবধান! ব্লগারদের চাপা 🤡',
                'description' => 'Overhyped - অতিরিক্ত হাইপ কিন্তু স্বাদ সাধারণ',
                'color' => 'rose',
            ];
        }

        return [
            'type' => 'neutral',
            'label' => 'মোটামুটি পপুলার ⚡',
            'description' => 'Community Tested',
            'color' => 'amber',
        ];
    }

    /**
     * Helper to determine budget tier by price.
     */
    public static function determineBudgetTier(int $price): string
    {
        if ($price < 150) {
            return 'budget';
        }

        if ($price <= 400) {
            return 'mid';
        }

        return 'luxury';
    }

    /**
     * Recalculate trust score based on votes.
     */
    public function recalculateTrustScore(): int
    {
        $this->trust_score = (int) (($this->hype_votes * 2) + ($this->meh_votes * 0) - ($this->scam_votes * 3));

        return $this->trust_score;
    }
}
