<?php

namespace App\Http\Requests\Employer;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePlanChequeCheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->isReferrer();
    }

    protected function prepareForValidation(): void
    {
        if (! $this->filled('payment_method')) {
            $this->merge(['payment_method' => 'netbanking']);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'plan_key' => [
                'required',
                'string',
                'max:32',
                Rule::exists('employer_plans', 'slug')->where(fn ($q) => $q->where('is_active', true)->where('is_custom_price', false)),
            ],
            'payment_method' => ['required', 'string', Rule::in(['netbanking'])],
            'utr_reference' => ['required', 'string', 'max:191'],
            'payment_date' => ['required', 'date', 'before_or_equal:today'],
            'agreement_accepted' => ['required', 'accepted'],
            'coupon_code' => ['nullable', 'string', 'max:64'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'plan_key.exists' => 'Please choose a valid purchasable plan.',
            'agreement_accepted.accepted' => 'You must accept the subscription agreement to continue.',
            'payment_date.before_or_equal' => 'Payment date cannot be in the future.',
            'utr_reference.required' => 'Please enter the UTR or transaction reference.',
        ];
    }
}
