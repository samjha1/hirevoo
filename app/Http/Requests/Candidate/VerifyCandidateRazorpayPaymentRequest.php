<?php

namespace App\Http\Requests\Candidate;

use Illuminate\Foundation\Http\FormRequest;

class VerifyCandidateRazorpayPaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isCandidate() ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'razorpay_order_id' => ['required', 'string', 'max:191'],
            'razorpay_payment_id' => ['required', 'string', 'max:191'],
            'razorpay_signature' => ['required', 'string', 'max:255'],
        ];
    }
}
