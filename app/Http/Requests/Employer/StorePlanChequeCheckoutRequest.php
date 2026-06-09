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
            'cheque_number' => ['required', 'string', 'max:191'],
            'cheque_date' => ['required', 'date', 'before_or_equal:today'],
            'agreement_accepted' => ['required', 'accepted'],
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
            'cheque_date.before_or_equal' => 'Cheque date cannot be in the future.',
        ];
    }
}
