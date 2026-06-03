<?php

namespace App\Http\Requests\Employer;

use Illuminate\Foundation\Http\FormRequest;

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
            'plan_key' => ['required', 'string', 'max:32'],
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
            'agreement_accepted.accepted' => 'You must accept the subscription agreement to continue.',
            'cheque_date.before_or_equal' => 'Cheque date cannot be in the future.',
        ];
    }
}
