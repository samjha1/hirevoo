<?php

namespace App\Http\Requests\Employer;

use App\Services\EmployerPlanCheckoutService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use InvalidArgumentException;

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
                function (string $attribute, mixed $value, \Closure $fail): void {
                    try {
                        app(EmployerPlanCheckoutService::class)->resolvePurchasablePlan((string) $value);
                    } catch (InvalidArgumentException $e) {
                        $fail($e->getMessage());
                    }
                },
            ],
            'coupon_code' => ['nullable', 'string', 'max:64'],
            'billing_months' => ['nullable', 'integer', Rule::in(config('hirevo_plans.billing_duration_options', [1, 3, 6, 12]))],
        ];
    }
}
