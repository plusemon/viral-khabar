<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreFoodSpotRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'dish_name' => ['required', 'string', 'max:255'],
            'shop_name' => ['required', 'string', 'max:255'],
            'area_name' => ['required', 'string', 'max:255'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'price' => ['required', 'integer', 'min:0', 'max:100000'],
            'category' => ['required', 'string', 'in:biriyani,street_food,cha_adda,kebab_chap,dessert'],
            'vibe' => ['required', 'string', 'in:tong,hangout_seating,late_night,takeaway'],
            'best_time' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * Get custom error messages for attributes.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'dish_name.required' => 'খাবারের নাম দিতে হবে (Dish name is required)',
            'shop_name.required' => 'দোকান বা রেস্তোরাঁর নাম দিন (Shop name is required)',
            'area_name.required' => 'এলাকা বা লোকেশন দিন (Area name is required)',
            'latitude.required' => 'ম্যাপ থেকে সঠিক লোকেশন সিলেক্ট করুন',
            'price.required' => 'আনুমানিক দাম (টাকায়) উল্লেখ করুন',
            'category.required' => 'একটি ক্যাটাগরি সিলেক্ট করুন',
            'vibe.required' => 'পরিবেশ বা ভাইব সিলেক্ট করুন',
        ];
    }
}
