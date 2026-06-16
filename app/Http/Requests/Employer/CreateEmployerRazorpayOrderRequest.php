<?php

namespace App\Http\Requests\Employer;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateEmployerRazorpayOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isReferrer() ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'plan_key' => [
                'required',
                'string',
                'max:32',
                Rule::exists('employer_plans', 'slug')->where(fn ($q) => $q->where('is_active', true)->where('is_custom_price', false)),
            ],
            'coupon_code' => ['nullable', 'string', 'max:64'],
        ];
    }
}
