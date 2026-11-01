<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('food_spots', function (Blueprint $table) {
            $table->id();
            $table->string('dish_name');
            $table->string('shop_name');
            $table->string('area_name')->index();
            $table->decimal('latitude', 10, 7)->index();
            $table->decimal('longitude', 10, 7)->index();
            $table->unsignedInteger('price');
            $table->string('budget_tier')->default('budget'); // budget (<150), mid (150-400), luxury (400+)
            $table->string('category')->index(); // biriyani, street_food, cha_adda, kebab_chap, dessert
            $table->string('vibe'); // tong, hangout_seating, late_night, takeaway
            $table->string('best_time')->nullable();
            $table->unsignedInteger('hype_votes')->default(1);
            $table->unsignedInteger('meh_votes')->default(0);
            $table->unsignedInteger('scam_votes')->default(0);
            $table->integer('trust_score')->default(2)->index();
            $table->string('status')->default('active'); // active, flagged
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('food_spots');
    }
};
