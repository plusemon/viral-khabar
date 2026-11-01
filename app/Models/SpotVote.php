<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SpotVote extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'food_spot_id',
        'voter_hash',
        'vote_type',
        'short_review',
    ];

    /**
     * The food spot that was voted on.
     */
    public function foodSpot(): BelongsTo
    {
        return $this->belongsTo(FoodSpot::class);
    }
}
