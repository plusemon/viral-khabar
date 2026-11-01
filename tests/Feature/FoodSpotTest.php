<?php

namespace Tests\Feature;

use App\Models\FoodSpot;
use App\Models\SpotVote;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FoodSpotTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_render_food_radar_home_page(): void
    {
        FoodSpot::create([
            'dish_name' => 'খাসির গ্লাসি',
            'shop_name' => 'আল-রাজ্জাক',
            'area_name' => 'পুরান ঢাকা',
            'latitude' => 23.7176,
            'longitude' => 90.4042,
            'price' => 380,
            'budget_tier' => 'mid',
            'category' => 'biriyani',
            'vibe' => 'late_night',
            'best_time' => 'রাত ৯টা - ১টা',
            'hype_votes' => 10,
            'meh_votes' => 1,
            'scam_votes' => 0,
            'trust_score' => 20,
            'status' => 'active',
        ]);

        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Index')
            ->has('spots', 1)
            ->has('filters')
            ->has('meta')
        );
    }

    public function test_can_store_new_food_spot_with_auto_budget_tier(): void
    {
        $payload = [
            'dish_name' => 'স্পেশাল মালাই চা',
            'shop_name' => 'খোকন মামার টং',
            'area_name' => 'টিএসসি',
            'latitude' => 23.7324,
            'longitude' => 90.3958,
            'price' => 30, // < 150 should auto determine budget tier
            'category' => 'cha_adda',
            'vibe' => 'tong',
            'best_time' => 'বিকেল ৫টা',
        ];

        $response = $this->post('/spots', $payload);

        $response->assertRedirect();
        $this->assertDatabaseHas('food_spots', [
            'dish_name' => 'স্পেশাল মালাই চা',
            'shop_name' => 'খোকন মামার টং',
            'budget_tier' => 'budget',
            'hype_votes' => 1,
            'trust_score' => 2,
        ]);

        $spot = FoodSpot::first();
        $this->assertNotNull($spot);
        $this->assertEquals(1, $spot->votes()->count());
    }

    public function test_validates_required_fields_when_storing_spot(): void
    {
        $response = $this->post('/spots', []);

        $response->assertSessionHasErrors([
            'dish_name',
            'shop_name',
            'area_name',
            'latitude',
            'longitude',
            'price',
            'category',
            'vibe',
        ]);
    }

    public function test_can_cast_hype_vote_and_recalculate_trust_score(): void
    {
        $spot = FoodSpot::create([
            'dish_name' => 'বিফ চাপ',
            'shop_name' => 'মুস্তাকিম',
            'area_name' => 'মোহাম্মদপুর',
            'latitude' => 23.7588,
            'longitude' => 90.3644,
            'price' => 180,
            'budget_tier' => 'mid',
            'category' => 'kebab_chap',
            'vibe' => 'street_food',
            'hype_votes' => 2,
            'meh_votes' => 0,
            'scam_votes' => 0,
            'trust_score' => 4,
            'status' => 'active',
        ]);

        $response = $this->post("/spots/{$spot->id}/vote", [
            'vote_type' => 'hype',
            'short_review' => 'অসাধারণ স্বাদ!',
            'voter_fingerprint' => 'test-fp-1',
        ]);

        $response->assertRedirect();

        $spot->refresh();
        $this->assertEquals(3, $spot->hype_votes);
        $this->assertEquals(6, $spot->trust_score); // 3 * 2 = 6
        $this->assertDatabaseHas('spot_votes', [
            'food_spot_id' => $spot->id,
            'vote_type' => 'hype',
            'short_review' => 'অসাধারণ স্বাদ!',
        ]);
    }

    public function test_can_cast_scam_vote_and_reduces_trust_score(): void
    {
        $spot = FoodSpot::create([
            'dish_name' => 'আগুন ফুচকা',
            'shop_name' => 'ভাইরাল ফুচকা',
            'area_name' => 'উত্তরা',
            'latitude' => 23.8742,
            'longitude' => 90.3857,
            'price' => 180,
            'budget_tier' => 'mid',
            'category' => 'street_food',
            'vibe' => 'street_food',
            'hype_votes' => 1,
            'meh_votes' => 0,
            'scam_votes' => 0,
            'trust_score' => 2,
            'status' => 'active',
        ]);

        $response = $this->post("/spots/{$spot->id}/vote", [
            'vote_type' => 'scam',
            'short_review' => 'একদম বেকার, ব্লগের নাটক!',
            'voter_fingerprint' => 'test-fp-scam',
        ]);

        $response->assertRedirect();

        $spot->refresh();
        $this->assertEquals(1, $spot->hype_votes);
        $this->assertEquals(1, $spot->scam_votes);
        // Trust score = (1 * 2) - (1 * 3) = -1
        $this->assertEquals(-1, $spot->trust_score);
    }

    public function test_can_update_existing_vote_atomically(): void
    {
        $spot = FoodSpot::create([
            'dish_name' => 'দই ফুচকা',
            'shop_name' => 'সিরাজ চটপটি',
            'area_name' => 'বেইলি রোড',
            'latitude' => 23.7417,
            'longitude' => 90.4089,
            'price' => 110,
            'budget_tier' => 'budget',
            'category' => 'street_food',
            'vibe' => 'street_food',
            'hype_votes' => 1,
            'meh_votes' => 0,
            'scam_votes' => 0,
            'trust_score' => 2,
            'status' => 'active',
        ]);

        // First vote: meh
        $this->post("/spots/{$spot->id}/vote", [
            'vote_type' => 'meh',
            'voter_fingerprint' => 'same-user-fp',
        ]);

        $spot->refresh();
        $this->assertEquals(1, $spot->hype_votes);
        $this->assertEquals(1, $spot->meh_votes);

        // Same voter changes vote to hype
        $this->post("/spots/{$spot->id}/vote", [
            'vote_type' => 'hype',
            'short_review' => 'আগের চেয়ে ভালো লেগেছে!',
            'voter_fingerprint' => 'same-user-fp',
        ]);

        $spot->refresh();
        $this->assertEquals(2, $spot->hype_votes);
        $this->assertEquals(0, $spot->meh_votes);
        $this->assertEquals(4, $spot->trust_score);
        $this->assertEquals(1, SpotVote::where('food_spot_id', $spot->id)->count());
    }

    public function test_filters_spots_by_budget_and_category(): void
    {
        FoodSpot::create([
            'dish_name' => 'কাচ্চি প্ল্যাটার',
            'shop_name' => 'সুলতান',
            'area_name' => 'ধানমন্ডি',
            'latitude' => 23.7371,
            'longitude' => 90.3725,
            'price' => 499,
            'budget_tier' => 'luxury',
            'category' => 'biriyani',
            'vibe' => 'hangout_seating',
            'trust_score' => 20,
            'status' => 'active',
        ]);

        FoodSpot::create([
            'dish_name' => 'লাল চা',
            'shop_name' => 'টং',
            'area_name' => 'টিএসসি',
            'latitude' => 23.7324,
            'longitude' => 90.3958,
            'price' => 15,
            'budget_tier' => 'budget',
            'category' => 'cha_adda',
            'vibe' => 'tong',
            'trust_score' => 15,
            'status' => 'active',
        ]);

        // Filter luxury
        $response = $this->get('/?budget_tier=luxury');
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->has('spots', 1));

        // Filter cha_adda
        $responseCha = $this->get('/?category=cha_adda');
        $responseCha->assertStatus(200);
        $responseCha->assertInertia(fn ($page) => $page->has('spots', 1));
    }
}
