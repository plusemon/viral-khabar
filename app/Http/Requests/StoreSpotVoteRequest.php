<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreSpotVoteRequest extends FormRequest
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
            'vote_type' => ['required', 'string', 'in:hype,meh,scam'],
            'short_review' => ['nullable', 'string', 'max:140'],
            'voter_fingerprint' => ['nullable', 'string', 'max:255'],
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
            'vote_type.required' => 'একটি ভোট অপশন সিলেক্ট করুন (hype, meh, scam)',
            'vote_type.in' => 'সঠিক ভোট টাইপ সিলেক্ট করুন',
            'short_review.max' => 'রিভিউ সর্বোচ্চ ১৪০ অক্ষরের মধ্যে হতে হবে',
        ];
    }
}
